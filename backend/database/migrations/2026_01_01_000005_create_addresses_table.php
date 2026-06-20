<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('Uy'); // Uy, Ish, ...
            $table->text('address');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('entrance')->nullable(); // podyezd
            $table->string('floor')->nullable();
            $table->string('apartment')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
