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
    $table->string('section_code')->nullable()->after('sort_order'); 
    // SECTION_2, PEOPLE, CUSTOMERS, FINANCIAL, OPERATIONAL, VALUES

    $table->string('section_title')->nullable()->after('section_code');

    $table->boolean('is_default')->default(false)->after('section_title');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_target_items', function (Blueprint $table) {
            //
        });
    }
};
