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
        Schema::create('performance_targets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('performance_period_id')->constrained('performance_periods')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('hr_reviewer_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title'); // ICT OFFICER - PERFORMANCE TARGETS FOR THE PERIOD JAN – DEC 2026

            $table->enum('status', [
                'not_submitted',
                'submitted',
                'approved_by_assessor',
                'rejected_by_assessor',
                'reviewed_by_hr',
            ])->default('not_submitted');

            $table->text('assessor_general_comment')->nullable();
            $table->text('hr_general_comment')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('hr_reviewed_at')->nullable();

            $table->timestamps();

            $table->unique(['performance_period_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_targets');
    }
};