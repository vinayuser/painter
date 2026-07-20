<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('painter_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('painter_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('image_path');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('painter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('painter_portfolios');
    }
};
