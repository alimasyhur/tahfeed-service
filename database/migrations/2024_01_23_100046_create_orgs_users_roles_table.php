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
        Schema::create('orgs_users_roles', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('org_uuid');
            $table->string('org_name');
            $table->uuid('user_uuid');
            $table->uuid('role_uuid');
            $table->string('role_name');
            $table->tinyInteger('constant_value');
            $table->tinyInteger('is_active');
            $table->tinyInteger('is_confirmed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orgs_users_roles');
    }
};
