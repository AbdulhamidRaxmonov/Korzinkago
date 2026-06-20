<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->addresses()->orderByDesc('is_default')->latest()->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);

        if (! empty($data['is_default'])) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        // Birinchi manzil avtomatik default bo'ladi
        if ($request->user()->addresses()->count() === 0) {
            $data['is_default'] = true;
        }

        $address = $request->user()->addresses()->create($data);

        return response()->json($address, 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        abort_if($address->user_id !== $request->user()->id, 403);

        $data = $this->validateData($request);

        if (! empty($data['is_default'])) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return response()->json($address);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        abort_if($address->user_id !== $request->user()->id, 403);
        $address->delete();

        return response()->json(['success' => true]);
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'entrance' => ['nullable', 'string', 'max:20'],
            'floor' => ['nullable', 'string', 'max:20'],
            'apartment' => ['nullable', 'string', 'max:20'],
            'comment' => ['nullable', 'string', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }
}
