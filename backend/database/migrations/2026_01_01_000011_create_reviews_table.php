<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // product | courier
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating'); // 1..5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['type', 'product_id']);
            $table->index(['type', 'courier_id']);
        });

        // Mahsulot va kuryer uchun keshlangan reyting maydonlari
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->default(0)->after('sold_count');
            $table->unsignedInteger('reviews_count')->default(0)->after('rating');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->default(0)->after('vehicle_type');
            $table->unsignedInteger('reviews_count')->default(0)->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['rating', 'reviews_count']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rating', 'reviews_count']);
        });
        Schema::dropIfExists('reviews');
    }
};
