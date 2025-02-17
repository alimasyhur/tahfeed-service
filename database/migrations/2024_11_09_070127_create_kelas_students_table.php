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

        Schema::create('kelas_students', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('kelas_uuid');
            $table->uuid('student_uuid');
            $table->uuid('org_uuid');
            $table->string('notes')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_students');
    }
};
