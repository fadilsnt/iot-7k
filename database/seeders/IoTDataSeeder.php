<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SensorType;
use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class IoTDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Starting IoT Data Seeder...');

        // 1. Get atau Create User
        $user = User::firstOrCreate(
            ['email' => 'demo@iot-tkhs.com'],
            [
                'name' => 'Demo User',
                'email' => 'demo@iot-tkhs.com',
                'password' => bcrypt('password123'),
                'email_verified_at' => now()
            ]
        );
        $this->command->info("✅ User: {$user->email}");

        // 2. Create atau Get Sensor Types
        $temperatureType = SensorType::firstOrCreate(
            ['slug' => 'temperature'],
            [
                'name' => 'Temperature',
                'slug' => 'temperature',
                'unit' => '°C',
                'icon' => '🌡️',
                'min_value' => 0,
                'max_value' => 100,
                'description' => 'Temperature sensor for measuring heat'
            ]
        );
        $this->command->info("✅ SensorType created: {$temperatureType->name}");

        $moistureType = SensorType::firstOrCreate(
            ['slug' => 'moisture'],
            [
                'name' => 'Moisture',
                'slug' => 'moisture',
                'unit' => '%',
                'icon' => '💧',
                'min_value' => 0,
                'max_value' => 100,
                'description' => 'Moisture sensor for measuring humidity level'
            ]
        );
        $this->command->info("✅ SensorType created: {$moistureType->name}");

        // 3. Create atau Get Sensors
        $tempSensor = Sensor::firstOrCreate(
            ['sensor_code' => 'TEMP_01'],
            [
                'user_id' => $user->id,
                'sensor_type_id' => $temperatureType->id,
                'sensor_code' => 'TEMP_01',
                'name' => 'Temperature Sensor 1',
                'location' => 'Production Room A',
                'description' => 'Primary temperature monitoring sensor',
                'status' => 'active'
            ]
        );
        $this->command->info("✅ Sensor created: {$tempSensor->name}");

        $moistSensor = Sensor::firstOrCreate(
            ['sensor_code' => 'MOIST_01'],
            [
                'user_id' => $user->id,
                'sensor_type_id' => $moistureType->id,
                'sensor_code' => 'MOIST_01',
                'name' => 'Moisture Sensor 1',
                'location' => 'Production Room A',
                'description' => 'Primary moisture/humidity monitoring sensor',
                'status' => 'active'
            ]
        );
        $this->command->info("✅ Sensor created: {$moistSensor->name}");

        // 5. Hapus data lama (optional - comment jika tidak ingin hapus)
        $this->command->info('🗑️  Cleaning old sensor readings...');
        SensorReading::whereIn('sensor_id', [$tempSensor->id, $moistSensor->id])->delete();

        // 6. Generate Data untuk 7 hari terakhir (setiap 1 jam = 168 data points per sensor)
        $this->command->info('📊 Generating sensor readings for 7 days...');
        
        $startDate = Carbon::now()->subDays(7);
        $dataCount = 168; // 7 hari * 24 jam
        
        $progressBar = $this->command->getOutput()->createProgressBar($dataCount * 2);
        $progressBar->start();

        for ($i = 0; $i < $dataCount; $i++) {
            $timestamp = $startDate->copy()->addHours($i);

            // Temperature readings (20°C - 35°C dengan variasi realistis)
            $baseTemp = 27; // Base temperature
            $variation = sin($i / 12) * 5; // Variasi siang/malam
            $randomNoise = (rand(-10, 10) / 10); // Random noise ±1°C
            $tempValue = $baseTemp + $variation + $randomNoise;

            SensorReading::create([
                'sensor_id' => $tempSensor->id,
                'value' => round($tempValue, 2),
                'recorded_at' => $timestamp,
                'metadata' => [
                    'battery_level' => rand(80, 100),
                    'signal_strength' => rand(-70, -30)
                ]
            ]);
            $progressBar->advance();

            // Moisture readings (40% - 80% dengan variasi realistis)
            $baseMoisture = 60; // Base moisture
            $moistVariation = sin($i / 18) * 10; // Variasi lebih lambat
            $moistNoise = (rand(-20, 20) / 10); // Random noise ±2%
            $moistValue = $baseMoisture + $moistVariation + $moistNoise;

            SensorReading::create([
                'sensor_id' => $moistSensor->id,
                'value' => round($moistValue, 2),
                'recorded_at' => $timestamp,
                'metadata' => [
                    'battery_level' => rand(80, 100),
                    'signal_strength' => rand(-70, -30)
                ]
            ]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);
        
        // Summary
        $totalReadings = SensorReading::count();
        $this->command->info("✅ Seeder completed successfully!");
        $this->command->info("📊 Total sensor readings: {$totalReadings}");
        $this->command->info("🌡️  Temperature readings: {$dataCount}");
        $this->command->info("💧 Moisture readings: {$dataCount}");
        $this->command->table(
            ['User', 'Sensors', 'Total Readings'],
            [
                [$user->name, 2, $totalReadings]
            ]
        );
    }
}