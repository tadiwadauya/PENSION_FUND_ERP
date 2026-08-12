<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('performance_target_items', 'weight')) {
            Schema::table('performance_target_items', function (Blueprint $table) {
                $table->decimal('weight', 5, 2)
                    ->nullable()
                    ->after('target_description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('performance_target_items', 'weight')) {
            Schema::table('performance_target_items', function (Blueprint $table) {
                $table->dropColumn('weight');
            });
        }
    }
};