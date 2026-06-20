<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // KG-000123
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();

            // Status: new -> accepted -> assembling -> on_way -> delivered / cancelled
            $table->enum('status', [
                'new', 'accepted', 'assembling', 'ready', 'on_way', 'delivered', 'cancelled',
            ])->default('new');

            // To'lov
            $table->enum('payment_method', ['cash', 'payme', 'click', 'card'])->default('cash');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');

            // Manzil snapshot
            $table->text('delivery_address');
            $table->decimal('delivery_lat', 10, 7);
            $table->decimal('delivery_lng', 10, 7);
            $table->string('entrance')->nullable();
            $table->string('floor')->nullable();
            $table->string('apartment')->nullable();
            $table->text('comment')->nullable();
            $table->string('recipient_phone', 20)->nullable();

            // Narxlar
            $table->decimal('items_total', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('distance_km', 8, 2)->nullable();

            // Vaqtlar
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['courier_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->decimal('price', 12, 2);
            $table->decimal('quantity', 8, 3);
            $table->string('unit')->default('dona');
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
