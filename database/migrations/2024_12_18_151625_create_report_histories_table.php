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
        Schema::create('report_histories', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('student_uuid');
            $table->uuid('report_uuid');
            $table->uuid('org_uuid');
            $table->timestamp('date_input');
            $table->string('name');
            $table->string('description');
            $table->string('type_report');
            $table->uuid('juz_page_uuid');
            $table->uuid('teacher_uuid');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_histories');
    }
};
