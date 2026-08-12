<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_target_sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('performance_target_id')
                ->constrained('performance_targets')
                ->cascadeOnDelete();

            $table->string('section_code', 100);
            $table->string('section_title');

            $table->decimal('weight', 5, 2)->default(0);

            $table->unsignedInteger('sort_order')->default(1);

            $table->timestamps();

            $table->unique(
                ['performance_target_id', 'section_code'],
                'pt_sections_target_code_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_target_sections');
    }
};