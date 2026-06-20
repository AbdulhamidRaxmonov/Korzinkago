<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otp,
        protected SmsService $sms,
    ) {
    }

    /**
     * 1-qadam: telefon raqamga OTP yuborish.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?9?9?8?\d{9,12}$/'],
        ]);

        $result = $this->otp->sendCode($data['phone']);

        return response()->json($result, $result['success'] ? 200 : 429);
    }

    /**
     * 2-qadam: OTP tekshirish va token berish.
     * Agar foydalanuvchi yo'q bo'lsa, avtomatik yaratiladi.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $check = $this->otp->verifyCode($data['phone'], $data['code']);

        if (! $check['success']) {
            return response()->json($check, 422);
        }

        $phone = $this->sms->normalizePhone($data['phone']);

        $user = User::firstOrNew(['phone' => $phone]);
        $isNew = ! $user->exists;

        if ($isNew) {
            $user->name = $data['name'] ?? 'Foydalanuvchi';
            $user->role = 'user';
        }

        $user->phone_verified_at = now();
        $user->save();

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'is_new' => $isNew,
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Joriy foydalanuvchi ma'lumotlari.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Profilni yangilash.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'avatar' => ['sometimes', 'nullable', 'string'],
        ]);

        $user->update($data);

        return response()->json($user);
    }

    /**
     * Tizimdan chiqish (joriy token o'chiriladi).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Tizimdan chiqdingiz.']);
    }
}
