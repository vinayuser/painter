<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('is_active');
            $table->unsignedSmallInteger('experience_years')->nullable()->after('avatar');
            $table->string('experience_text', 50)->nullable()->after('experience_years');
            $table->decimal('cost_per_hour', 10, 2)->nullable()->after('experience_text');
            $table->string('aadhar_number', 12)->nullable()->after('cost_per_hour');
            $table->string('specialization', 150)->nullable()->after('aadhar_number');
            $table->string('license_number', 50)->nullable()->after('specialization');
            $table->string('vehicle_number', 20)->nullable()->after('license_number');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('phone');
            $table->unique('aadhar_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropUnique(['aadhar_number']);
            $table->dropColumn([
                'is_verified',
                'experience_years',
                'experience_text',
                'cost_per_hour',
                'aadhar_number',
                'specialization',
                'license_number',
                'vehicle_number',
            ]);
        });
    }
};
