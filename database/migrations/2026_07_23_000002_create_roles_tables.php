<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // super_admin, support, content, etc.
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('role_web_admin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('web_admin_id')->constrained('web_admins')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->unique(['web_admin_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_web_admin');
        Schema::dropIfExists('roles');
    }
};
