<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('payme'); // payme, click
            $table->string('transaction_id')->nullable()->index(); // Payme tranzaksiya id
            $table->decimal('amount', 14, 2); // tiyin/so'mda
            $table->enum('state', [
                'created', 'pending', 'paid', 'cancelled',
            ])->default('created');
            // Payme state codes: 1 (created), 2 (paid), -1, -2 (cancelled)
            $table->integer('payme_state')->nullable();
            $table->integer('reason')->nullable();
            $table->bigInteger('perform_time')->nullable();
            $table->bigInteger('cancel_time')->nullable();
            $table->bigInteger('create_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
