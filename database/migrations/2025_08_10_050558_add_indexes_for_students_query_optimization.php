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
        Schema::table('students', function (Blueprint $table) {
            // Index utama untuk WHERE deleted_at IS NULL
            $table->index('deleted_at', 'idx_students_deleted_at');

            // Index untuk JOIN conditions
            $table->index('org_uuid', 'idx_students_org_uuid');
            $table->index('grade_uuid', 'idx_students_grade_uuid');
            $table->index('user_uuid', 'idx_students_user_uuid');

            // Index untuk pencarian umum
            $table->index('nik', 'idx_students_nik');
            $table->index('nis', 'idx_students_nis');

            // Composite index untuk query yang sering digunakan
            $table->index(['deleted_at', 'org_uuid'], 'idx_students_active_org');
            $table->index(['deleted_at', 'grade_uuid'], 'idx_students_active_grade');
            $table->index(['deleted_at', 'org_uuid', 'grade_uuid'], 'idx_students_main_query');

            // Index untuk nama (jika sering di-search)
            $table->index('firstname', 'idx_students_firstname');
            $table->index('lastname', 'idx_students_lastname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_deleted_at');
            $table->dropIndex('idx_students_org_uuid');
            $table->dropIndex('idx_students_grade_uuid');
            $table->dropIndex('idx_students_user_uuid');
            $table->dropIndex('idx_students_nik');
            $table->dropIndex('idx_students_nis');
            $table->dropIndex('idx_students_active_org');
            $table->dropIndex('idx_students_active_grade');
            $table->dropIndex('idx_students_main_query');
            $table->dropIndex('idx_students_firstname');
            $table->dropIndex('idx_students_lastname');
        });
    }
};
