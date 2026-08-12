<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_assessment_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('performance_assessment_id')
                ->constrained('performance_assessments')
                ->cascadeOnDelete();

            $table->foreignId('performance_target_item_id')
                ->constrained('performance_target_items')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Snapshot of Target Structure
            |--------------------------------------------------------------------------
            |
            | We deliberately copy these values into the appraisal so that if a
            | target configuration later changes, the historical appraisal remains
            | exactly as it was during the assessment.
            |
            */

            $table->string('section_code', 100);
            $table->string('section_title');

            $table->text('task');

            $table->decimal('section_weight', 5, 2)->default(0);
            $table->decimal('item_weight', 5, 2)->default(0);

            $table->string('target_type', 50);
            $table->string('frequency', 50);

            $table->text('measure_target');

            $table->decimal('per_cycle_target_value', 15, 2)->nullable();
            $table->decimal('period_target_value', 15, 2)->nullable();

            $table->string('unit_of_measure')->nullable();
            $table->string('evaluation_method', 50);

            /*
            |--------------------------------------------------------------------------
            | Employee Actual Performance
            |--------------------------------------------------------------------------
            */

            $table->decimal('employee_actual_value', 15, 2)->nullable();

            $table->decimal('employee_achievement_percentage', 10, 2)->nullable();

            $table->foreignId('employee_rating_scale_id')
                ->nullable()
                ->constrained('performance_rating_scales')
                ->nullOnDelete();

            $table->decimal('employee_rating_score', 8, 4)->nullable();

            $table->text('employee_comment')->nullable();
            $table->text('employee_evidence')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Assessor Verification
            |--------------------------------------------------------------------------
            */

            $table->decimal('assessor_actual_value', 15, 2)->nullable();

            $table->decimal('assessor_achievement_percentage', 10, 2)->nullable();

            $table->foreignId('assessor_rating_scale_id')
                ->nullable()
                ->constrained('performance_rating_scales')
                ->nullOnDelete();

            $table->decimal('assessor_rating_score', 8, 4)->nullable();

            $table->text('assessor_comment')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Reviewer Verification
            |--------------------------------------------------------------------------
            */

            $table->decimal('reviewer_actual_value', 15, 2)->nullable();

            $table->decimal('reviewer_achievement_percentage', 10, 2)->nullable();

            $table->foreignId('reviewer_rating_scale_id')
                ->nullable()
                ->constrained('performance_rating_scales')
                ->nullOnDelete();

            $table->decimal('reviewer_rating_score', 8, 4)->nullable();

            $table->text('reviewer_comment')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Weighted Result
            |--------------------------------------------------------------------------
            */

            $table->decimal('employee_weighted_score', 12, 6)->nullable();
            $table->decimal('assessor_weighted_score', 12, 6)->nullable();
            $table->decimal('reviewer_weighted_score', 12, 6)->nullable();

            $table->timestamps();

            $table->unique([
                'performance_assessment_id',
                'performance_target_item_id',
            ], 'assessment_target_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_assessment_items');
    }
};