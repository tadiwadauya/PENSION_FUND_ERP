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
        Schema::create('performance_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. JANUARY TO JUNE 2026
            $table->string('year'); // e.g. 2026
            $table->enum('review_type', ['annual', 'bi_annual', 'quarterly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('review_start_date')->nullable();
            $table->date('review_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_periods');
    }
};