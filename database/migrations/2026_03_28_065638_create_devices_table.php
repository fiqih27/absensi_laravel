<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name', 50);
            $table->string('ip_address', 15);
            $table->integer('port')->default(4370);
            $table->string('serial_number')->nullable();
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->string('location', 100)->nullable(); // Lokasi pemasangan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
