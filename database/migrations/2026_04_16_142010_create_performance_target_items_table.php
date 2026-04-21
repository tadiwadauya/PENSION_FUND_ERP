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
        Schema::create('performance_target_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_target_id')->constrained('performance_targets')->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(1);

            $table->string('perspective')->nullable(); // e.g. Finance, Customer, Internal Process, Learning & Growth
            $table->text('task'); // what to achieve
            $table->text('how_to_achieve')->nullable(); // strategy / initiative
            $table->text('measure_target'); // measurable target
            $table->date('due_date')->nullable();

            $table->text('assessor_comment')->nullable();
            $table->text('hr_comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_target_items');
    }
};