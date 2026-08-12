<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_assessment_cycles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('performance_assessment_item_id');

            $table->foreign('performance_assessment_item_id', 'pa_cycles_item_fk')
                ->references('id')
                ->on('performance_assessment_items')
                ->cascadeOnDelete();

            $table->unsignedInteger('cycle_number');

            $table->string('cycle_label')->nullable();

            $table->date('cycle_start_date')->nullable();
            $table->date('cycle_end_date')->nullable();
            $table->date('due_date')->nullable();

            $table->decimal('target_value', 15, 2)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Employee
            |--------------------------------------------------------------------------
            */

            $table->decimal('employee_actual_value', 15, 2)->nullable();

            $table->boolean('employee_met_target')->nullable();

            $table->text('employee_comment')->nullable();
            $table->text('employee_evidence')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Assessor
            |--------------------------------------------------------------------------
            */

            $table->decimal('assessor_actual_value', 15, 2)->nullable();

            $table->boolean('assessor_met_target')->nullable();

            $table->text('assessor_comment')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Reviewer
            |--------------------------------------------------------------------------
            */

            $table->decimal('reviewer_actual_value', 15, 2)->nullable();

            $table->boolean('reviewer_met_target')->nullable();

            $table->text('reviewer_comment')->nullable();

            $table->timestamps();

            $table->unique(
                ['performance_assessment_item_id', 'cycle_number'],
                'pa_cycles_item_cycle_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_assessment_cycles');
    }
};