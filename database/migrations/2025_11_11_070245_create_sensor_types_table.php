<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Temperature", "Moisture", "pH", "Light"
            $table->string('slug')->unique(); // "temperature", "moisture", "ph", "light"
            $table->string('unit'); // "°C", "%", "pH", "lux"
            $table->string('icon')->nullable(); // "🌡️", "💧", etc
            $table->decimal('min_value', 10, 2)->nullable(); // Nilai minimum normal
            $table->decimal('max_value', 10, 2)->nullable(); // Nilai maximum normal
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_types');
    }
};