<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('disk')->default('medical'); // medical private disk
            $table->string('category'); // prescriptions|insurance|medical_reports|doctor_documents|pharmacy_documents
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('owner_firebase_uid')->nullable()->index();
            $table->string('owner_role')->nullable(); // patient|doctor|pharmacy|admin
            $table->string('related_type')->nullable();
            $table->string('related_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['category', 'owner_firebase_uid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
