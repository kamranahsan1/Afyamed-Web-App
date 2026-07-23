<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('care_plans', function (Blueprint $table) {
            $table->string('category')->default('clinical')->after('slug'); // membership|clinical|wellness
            $table->string('tagline')->nullable()->after('summary');
            $table->json('benefits')->nullable()->after('body');
            $table->json('member_events')->nullable()->after('benefits');
        });
    }

    public function down(): void
    {
        Schema::table('care_plans', function (Blueprint $table) {
            $table->dropColumn(['category', 'tagline', 'benefits', 'member_events']);
        });
    }
};
