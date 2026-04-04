<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 20)->unique(); // Nomor WA pengirim
            $table->string('contact_name')->nullable(); // Nama kontak
            $table->text('last_message')->nullable(); // Pesan terakhir
            $table->timestamp('last_message_at')->nullable(); // Waktu pesan terakhir
            $table->integer('unread_count')->default(0); // Jumlah pesan belum dibaca
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_conversations');
    }
};
