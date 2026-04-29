<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            // Add status column (enum)
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active')->after('description');
            
            // Drop unnecessary columns
            $table->dropColumn([
                'model',
                'unit',
                'min_value',
                'max_value',
                'pin_number',
                'is_active',
                'last_seen_at',
                'calibration_data'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            // Remove status column
            $table->dropColumn('status');
            
            // Add back old columns
            $table->string('model')->nullable();
            $table->string('unit')->nullable();
            $table->float('min_value')->nullable();
            $table->float('max_value')->nullable();
            $table->integer('pin_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->json('calibration_data')->nullable();
        });
    }
};
