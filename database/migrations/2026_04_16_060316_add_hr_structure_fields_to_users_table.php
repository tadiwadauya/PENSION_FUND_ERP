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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('mobile')->nullable()->after('email');
            $table->string('extension')->nullable()->after('mobile');
            $table->string('address')->nullable()->after('extension');
            $table->string('gender')->nullable()->after('address');
            $table->date('dob')->nullable()->after('gender');
            $table->string('job_title')->nullable()->after('dob');
            $table->integer('grade')->nullable()->after('job_title');

            $table->foreignId('department_id')->nullable()->after('grade')->constrained('departments')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->after('department_id')->constrained('sections')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->after('section_id')->constrained('users')->nullOnDelete();
           $table->foreignId('reviewer_id')->nullable()->after('supervisor_id')->constrained('users')->nullOnDelete();

            $table->boolean('is_admin')->default(false)->after('reviewer_id');
            $table->boolean('is_hr')->default(false)->after('is_admin');
            $table->boolean('is_ceo')->default(false)->after('is_hr');
            $table->boolean('is_head_of_department')->default(false)->after('is_ceo');
            $table->boolean('is_head_of_section')->default(false)->after('is_head_of_department');

            $table->boolean('must_change_password')->default(false)->after('is_head_of_section');
            $table->timestamp('password_changed_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('section_id');
            $table->dropConstrainedForeignId('supervisor_id');
            $table->dropConstrainedForeignId('reviewer_id');

            $table->dropColumn([
                'username',
                'first_name',
                'last_name',
                'mobile',
                'extension',
                'address',
                'gender',
                'dob',
                'job_title',
                'grade',
                'is_admin',
                'is_hr',
                'is_ceo',
                'is_head_of_department',
                'is_head_of_section',
                'must_change_password',
                'otp_plain',
                'password_changed_at',
                'deleted_at',
            ]);
        });
    }
};