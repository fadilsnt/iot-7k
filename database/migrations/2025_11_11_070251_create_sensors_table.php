<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Sensor identification
            $table->string('sensor_code')->unique(); // Kode unik: "TEMP_01", "MOIST_01"
            $table->string('name'); // Nama custom: "Temperature Sensor A"
            $table->string('model')->nullable(); // Model sensor: "DHT22", "Capacitive Soil"
            $table->string('location')->nullable(); // Lokasi: "Room A", "Warehouse"
            $table->text('description')->nullable(); // Deskripsi sensor
            
            // Sensor specifications
            $table->string('unit')->nullable(); // Satuan: °C, %, ppm
            $table->float('min_value')->nullable(); // Nilai minimum
            $table->float('max_value')->nullable(); // Nilai maksimum
            $table->integer('pin_number')->nullable(); // Pin di hardware: GPIO4
            
            // Sensor status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable(); // Terakhir dilihat online
            $table->json('calibration_data')->nullable(); // Data kalibrasi jika ada
            
            $table->timestamps();
            
            $table->index('sensor_code');
            $table->index('sensor_type_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};