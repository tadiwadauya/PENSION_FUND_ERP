<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_assessments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('performance_target_id')
                ->constrained('performance_targets')
                ->cascadeOnDelete();

            $table->foreignId('performance_period_id')
                ->constrained('performance_periods')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('assessor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('reviewer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('hr_reviewer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');

            $table->enum('status', [
                'not_started',
                'self_assessment_in_progress',
                'submitted_by_employee',
                'assessed_by_assessor',
                'rejected_by_assessor',
                'submitted_to_reviewer',
                'reviewed',
                'completed',
            ])->default('not_started');

            $table->text('employee_general_comment')->nullable();
            $table->text('assessor_general_comment')->nullable();
            $table->text('reviewer_general_comment')->nullable();
            $table->text('hr_general_comment')->nullable();

            $table->timestamp('employee_submitted_at')->nullable();
            $table->timestamp('assessor_assessed_at')->nullable();
            $table->timestamp('reviewer_reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique('performance_target_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_assessments');
    }
};