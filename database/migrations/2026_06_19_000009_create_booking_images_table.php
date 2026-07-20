<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('painter_booking_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('type')->index();
            $table->timestamps();

            $table->index(['painter_booking_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_images');
    }
};
