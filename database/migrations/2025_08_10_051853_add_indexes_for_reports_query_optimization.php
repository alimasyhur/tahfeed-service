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
        Schema::table('reports', function (Blueprint $table) {
            // Primary index for filtering and joining
            $table->index(['deleted_at', 'student_uuid'], 'idx_reports_student_deleted');

            // Index for date filtering
            $table->index('date_input', 'idx_reports_date_input');

            // Index for teacher filtering
            $table->index('teacher_uuid', 'idx_reports_teacher');

            // Index for kelas filtering
            $table->index('kelas_uuid', 'idx_reports_kelas');

            // Index for organization filtering
            $table->index('org_uuid', 'idx_reports_org');

            // Covering index for better performance (includes commonly selected columns)
            $table->index([
                'deleted_at',
                'student_uuid',
                'start_juz_page_uuid',
                'end_juz_page_uuid'
            ], 'idx_reports_covering');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('idx_reports_student_deleted');
            $table->dropIndex('idx_reports_date_input');
            $table->dropIndex('idx_reports_teacher');
            $table->dropIndex('idx_reports_kelas');
            $table->dropIndex('idx_reports_org');
            $table->dropIndex('idx_reports_covering');
        });
    }
};
