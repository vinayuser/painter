<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('painter_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('painter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('booking_date');
            $table->time('booking_time');
            $table->text('address');
            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['painter_id', 'status']);
            $table->index('booking_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('painter_bookings');
    }
};
