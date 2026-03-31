<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nisn', 20)->unique(); // Nomor Induk Siswa Nasional
            $table->string('name', 100);
            $table->string('class', 20);
            $table->string('fingerprint_uid', 20)->nullable(); // UID dari mesin fingerprint
            $table->string('device_user_id', 20)->nullable(); // User ID di mesin ZKTeco
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
