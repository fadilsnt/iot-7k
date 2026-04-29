<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained()->onDelete('cascade');
            $table->decimal('value', 10, 2); // Nilai pembacaan
            $table->timestamp('recorded_at')->useCurrent(); // Waktu pembacaan
            $table->json('metadata')->nullable(); // Data tambahan (battery level, signal strength, dll)
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index('sensor_id');
            $table->index('recorded_at');
            $table->index(['sensor_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};