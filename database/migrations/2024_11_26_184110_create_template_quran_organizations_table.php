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
        Schema::create('template_quran_organizations', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->uuid('template_quran_uuid');
            $table->uuid('org_uuid');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_quran_organizations');
    }
};
