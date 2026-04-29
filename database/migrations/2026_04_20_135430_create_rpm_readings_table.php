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
        Schema::create('rpm_readings', function (Blueprint $table) {
            $table->id();
            $table->string('rpm_id'); // ID atau Nama Mesin
            $table->timestamp('recorded_at')->useCurrent(); // Timestamp sensor
            $table->decimal('rpm', 8, 2)->default(0);
            $table->decimal('hm', 10, 2)->default(0);
            $table->decimal('temp', 5, 2)->default(0);
            $table->decimal('humi', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpm_readings');
    }
};
