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
        Schema::create('template_quran_juz_pages', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('template_quran_juz_uuid');
            $table->uuid('template_quran_page_uuid');
            $table->string('description');
            $table->integer('constant');
            $table->integer('value');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_quran_juz_pages');
    }
};
