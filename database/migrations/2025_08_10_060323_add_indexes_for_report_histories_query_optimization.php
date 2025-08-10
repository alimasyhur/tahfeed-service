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
        Schema::table('report_histories', function (Blueprint $table) {
            // 1. PRIMARY INDEX: Most critical for your query
            $table->index(['org_uuid', 'student_uuid'], 'idx_rh_org_student');

            // 2. INDEX for date filtering
            $table->index(['date_input', 'type_report'], 'idx_rh_date_type');

            // 3. COMPREHENSIVE INDEX: Best performance for this query
            $table->index([
                'org_uuid',
                'student_uuid',
                'type_report',
                'date_input'
            ], 'idx_rh_comprehensive');

            // 4. COVERING INDEX: Includes juz_page_uuid for COUNT DISTINCT
            $table->index([
                'org_uuid',
                'student_uuid',
                'date_input',
                'type_report',
                'juz_page_uuid'
            ], 'idx_rh_covering');

            // 5. Simple org filter index
            $table->index('org_uuid', 'idx_rh_org');

            // 6. Deleted_at index for soft deletes
            $table->index('deleted_at', 'idx_rh_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_histories', function (Blueprint $table) {
            $table->dropIndex('idx_rh_org_student');
            $table->dropIndex('idx_rh_date_type');
            $table->dropIndex('idx_rh_comprehensive');
            $table->dropIndex('idx_rh_covering');
            $table->dropIndex('idx_rh_org');
            $table->dropIndex('idx_rh_deleted_at');
        });
    }
};
