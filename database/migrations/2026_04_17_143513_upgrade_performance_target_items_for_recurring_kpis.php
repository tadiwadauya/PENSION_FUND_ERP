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
        Schema::table('performance_target_items', function (Blueprint $table) {
            $table->enum('target_type', ['one_time', 'recurring'])
                ->default('one_time')
                ->after('perspective');

            $table->enum('frequency', ['once', 'daily', 'weekly', 'monthly', 'quarterly', 'annual'])
                ->default('once')
                ->after('target_type');

            $table->unsignedTinyInteger('due_day')
                ->nullable()
                ->after('frequency'); // e.g. 5 = 5th day of month

            $table->unsignedTinyInteger('due_month')
                ->nullable()
                ->after('due_day'); // e.g. 12 = December for annual targets

            $table->unsignedTinyInteger('due_weekday')
                ->nullable()
                ->after('due_month'); // 1=Mon ... 7=Sun

            $table->decimal('per_cycle_target_value', 15, 2)
                ->nullable()
                ->after('measure_target');

            $table->decimal('period_target_value', 15, 2)
                ->nullable()
                ->after('per_cycle_target_value');

            $table->string('unit_of_measure')
                ->nullable()
                ->after('period_target_value'); // %, USD, count, days, properties

            $table->enum('evaluation_method', ['per_cycle', 'cumulative', 'average'])
                ->default('per_cycle')
                ->after('unit_of_measure');

            $table->text('target_description')
                ->nullable()
                ->after('evaluation_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_target_items', function (Blueprint $table) {
            $table->dropColumn([
                'target_type',
                'frequency',
                'due_day',
                'due_month',
                'due_weekday',
                'per_cycle_target_value',
                'period_target_value',
                'unit_of_measure',
                'evaluation_method',
                'target_description',
            ]);
        });
    }
};