<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama device: "Greenhouse 1", "Room A"
            $table->string('device_id')->unique(); // ID unik dari hardware: "ESP32_001"
            $table->string('model')->nullable(); // Model: "ESP32", "Arduino Uno"
            $table->text('description')->nullable(); // Deskripsi device
            $table->string('location')->nullable(); // Lokasi fisik: "Greenhouse A"
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamp('last_seen_at')->nullable(); // Terakhir kirim data
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Owner device
            $table->timestamps();
            
            $table->index('device_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};