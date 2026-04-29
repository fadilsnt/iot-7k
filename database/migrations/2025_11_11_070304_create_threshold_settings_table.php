<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threshold_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Nama threshold: "High Temperature Alert"
            $table->decimal('min_value', 10, 2)->nullable(); // Nilai minimum
            $table->decimal('max_value', 10, 2)->nullable(); // Nilai maximum
            $table->enum('operator', ['between', 'less_than', 'greater_than', 'equal_to']);
            $table->boolean('is_active')->default(true);
            $table->string('notification_method')->default('email'); // email, sms, push
            $table->string('notification_email')->nullable();
            $table->string('notification_phone')->nullable();
            $table->integer('cooldown_minutes')->default(30); // Cooldown untuk tidak spam notif
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
            
            $table->index('sensor_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('threshold_settings');
    }
};