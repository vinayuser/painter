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
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delivery_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_status')->default('pending')->index();
            $table->string('order_status')->default('pending')->index();
            $table->string('delivery_status')->default('pending')->index();
            $table->text('shipping_address');
            $table->string('shipping_phone', 20)->nullable();
            $table->text('notes')->nullable();
            $table->string('delivery_proof_path')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'order_status']);
            $table->index(['delivery_agent_id', 'delivery_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
