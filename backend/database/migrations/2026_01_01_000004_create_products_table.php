<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_ru')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('old_price', 12, 2)->nullable();
            $table->string('unit')->default('dona'); // dona, kg, litr
            $table->decimal('step', 8, 3)->default(1); // 1 yoki 0.1 (kg uchun)
            $table->unsignedInteger('stock')->default(0);
            $table->string('barcode')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sold_count')->default(0);
            $table->timestamps();

            $table->index(['category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
