<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('whatsapp_conversations')->onDelete('cascade');
            $table->string('message_id')->nullable(); // ID dari WhatsApp
            $table->enum('direction', ['incoming', 'outgoing']); // masuk atau keluar
            $table->text('message');
            $table->string('status')->default('sent'); // sent, delivered, read, failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
