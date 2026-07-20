<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_name')->nullable()->after('name');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('category_id')->constrained('users')->nullOnDelete();
            $table->index(['vendor_id', 'is_active']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            $table->string('payment_method')->default('online')->after('total_amount');
            $table->string('vendor_packing_status')->default('pending')->after('delivery_status');
            $table->timestamp('packing_deadline_at')->nullable()->after('vendor_packing_status');
            $table->timestamp('packed_at')->nullable()->after('packing_deadline_at');
            $table->timestamp('delivery_deadline_at')->nullable()->after('packed_at');

            $table->index(['vendor_id', 'order_status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn([
                'vendor_id', 'payment_method', 'vendor_packing_status',
                'packing_deadline_at', 'packed_at', 'delivery_deadline_at',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn('vendor_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('business_name');
        });
    }
};
