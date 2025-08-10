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
        // Add indexes to kelas table
        Schema::table('kelas', function (Blueprint $table) {
            // Index for WHERE deleted_at IS NULL condition
            $table->index('deleted_at', 'idx_kelas_deleted_at');

            // Indexes for JOIN conditions
            $table->index('org_uuid', 'idx_kelas_org_uuid');
            $table->index('grade_uuid', 'idx_kelas_grade_uuid');
            $table->index('teacher_uuid', 'idx_kelas_teacher_uuid');

            // Composite index for optimal performance - START with deleted_at for WHERE clause
            $table->index(['deleted_at', 'teacher_uuid', 'org_uuid', 'grade_uuid'], 'idx_kelas_main_query');
        });

        // Add index to teachers table - PENTING untuk menghindari ALL scan
        Schema::table('teachers', function (Blueprint $table) {
            $table->index('user_uuid', 'idx_teachers_user_uuid');
            // Tambah index untuk deleted_at jika ada soft delete di teachers
            $table->index('deleted_at', 'idx_teachers_deleted_at');
        });

        // Add index to grades table - untuk menghindari ALL scan
        Schema::table('grades', function (Blueprint $table) {
            $table->index('deleted_at', 'idx_grades_deleted_at');
            $table->index('org_uuid', 'idx_grades_org_uuid');
        });

        // Add index to organizations table - untuk optimasi lebih lanjut
        Schema::table('organizations', function (Blueprint $table) {
            $table->index('deleted_at', 'idx_organizations_deleted_at');
        });

        // Add index to users table - untuk soft delete
        Schema::table('users', function (Blueprint $table) {
            $table->index('deleted_at', 'idx_users_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from kelas table
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropIndex('idx_kelas_deleted_at');
            $table->dropIndex('idx_kelas_org_uuid');
            $table->dropIndex('idx_kelas_grade_uuid');
            $table->dropIndex('idx_kelas_teacher_uuid');
            $table->dropIndex('idx_kelas_main_query');
        });

        // Drop indexes from teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex('idx_teachers_user_uuid');
            $table->dropIndex('idx_teachers_deleted_at');
        });

        // Drop indexes from grades table
        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex('idx_grades_deleted_at');
            $table->dropIndex('idx_grades_org_uuid');
        });

        // Drop indexes from organizations table
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('idx_organizations_deleted_at');
        });

        // Drop indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_deleted_at');
        });
    }
};
