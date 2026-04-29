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
            // Threshold parameters (nullable - if null, no threshold monitoring)
            // Normal range: threshold_min_1 < value < threshold_plus_1
            // Warning range: (threshold_plus_1 < value < threshold_plus_2) OR (threshold_min_2 < value < threshold_min_1)
            // Danger: value > threshold_plus_2 OR value < threshold_min_2
            
            $table->float('threshold_plus_1', 8, 2)->nullable()->after('description')
                ->comment('Threshold +1: Upper limit for normal range');
            $table->float('threshold_plus_2', 8, 2)->nullable()->after('threshold_plus_1')
                ->comment('Threshold +2: Upper limit for warning range (above = danger)');
            $table->float('threshold_min_1', 8, 2)->nullable()->after('threshold_plus_2')
                ->comment('Threshold -1: Lower limit for normal range');
            $table->float('threshold_min_2', 8, 2)->nullable()->after('threshold_min_1')
                ->comment('Threshold -2: Lower limit for warning range (below = danger)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropColumn([
                'threshold_plus_1',
                'threshold_plus_2', 
                'threshold_min_1',
                'threshold_min_2'
            ]);
        });
    }
};
