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
        Schema::create('surat_requests', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique();
            $table->string('nama_pengirim');
            $table->string('email_pengirim')->nullable();
            $table->string('perihal');
            $table->text('tujuan');
            $table->string('asal_instansi')->nullable();
            $table->string('jenis_surat');
            $table->string('file_surat');
            $table->enum('status', ['pending', 'diteruskan', 'diproses', 'selesai'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_requests');
    }
};
