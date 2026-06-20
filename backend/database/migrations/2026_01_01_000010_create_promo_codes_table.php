<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percent', 'fixed'])->default('percent'); // foiz yoki qat'iy summa
            $table->decimal('value', 12, 2); // 10 (%) yoki 20000 (so'm)
            $table->decimal('min_order', 12, 2)->default(0); // minimal buyurtma summasi
            $table->decimal('max_discount', 12, 2)->nullable(); // foiz uchun maksimal chegirma
            $table->unsignedInteger('usage_limit')->nullable(); // umumiy limit (null = cheksiz)
            $table->unsignedInteger('used_count')->default(0);
            $table->unsignedInteger('per_user_limit')->default(1); // bir foydalanuvchi necha marta
            $table->boolean('first_order_only')->default(false); // faqat birinchi buyurtma uchun
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('promo_code_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount', 12, 2);
            $table->timestamps();

            $table->index(['promo_code_id', 'user_id']);
        });

        // orders jadvaliga promo_code ustunini qo'shish
        Schema::table('orders', function (Blueprint $table) {
            $table->string('promo_code')->nullable()->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('promo_code');
        });
        Schema::dropIfExists('promo_code_usages');
        Schema::dropIfExists('promo_codes');
    }
};
