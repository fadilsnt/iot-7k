<?php

namespace App\Console\Commands;

use App\Models\Sensor;
use Illuminate\Console\Command;

class UpdateSensorConditions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sensors:update-conditions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all sensor conditions based on their latest readings and thresholds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating sensor conditions...');

        $sensors = Sensor::all();
        $updated = 0;

        foreach ($sensors as $sensor) {
            $condition = $sensor->updateCondition();
            $this->line("Sensor {$sensor->sensor_code}: {$sensor->getConditionName()} (condition={$condition})");
            $updated++;
        }

        $this->info("Successfully updated {$updated} sensor(s).");
        return Command::SUCCESS;
    }
}
