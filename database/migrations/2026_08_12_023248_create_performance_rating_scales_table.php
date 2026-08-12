<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_rating_scales', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20); // A1, A2, B1, B2, C1, C2
            $table->unsignedTinyInteger('score'); // 6,5,4,3,2,1

            $table->decimal('min_percentage', 8, 2);
            $table->decimal('max_percentage', 8, 2);

            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique('code');
            $table->unique('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_rating_scales');
    }
};