<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('firebase_uid')->nullable()->index();
            $table->string('role')->nullable(); // patient|doctor|pharmacy
            $table->string('category')->default('general');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('message');
            $table->string('status')->default('open'); // open|reviewed|closed
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('web_admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
