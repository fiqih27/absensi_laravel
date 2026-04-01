<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBroadcastHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('broadcast_histories', function (Blueprint $table) {
            $table->id();
            $table->string('broadcast_id')->unique(); // ID unik untuk setiap broadcast
            $table->text('message'); // Pesan yang dikirim
            $table->string('recipient_type'); // 'all' atau 'active_only'
            $table->integer('total_recipients'); // Total penerima
            $table->integer('sent_count')->default(0); // Jumlah berhasil
            $table->integer('failed_count')->default(0); // Jumlah gagal
            $table->json('recipients_detail')->nullable(); // Detail penerima dalam format JSON
            $table->json('failed_recipients')->nullable(); // Detail penerima yang gagal
            $table->string('status')->default('processing'); // processing, completed, failed
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Index untuk performa query
            $table->index(['status', 'created_at']);
            $table->index('broadcast_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('broadcast_histories');
    }
}
