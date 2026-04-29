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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained()->onDelete('cascade');
            $table->string('sensor_code');
            $table->string('sensor_name');
            $table->string('sensor_type');
            $table->tinyInteger('condition')->comment('1=Normal, 2=Warning, 3=Danger');
            $table->decimal('sensor_value', 10, 2)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
            
            $table->index(['sensor_id', 'created_at']);
            $table->index('condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
