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
        Schema::create('contribution_monthlies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('house_id')->constrained();
            $table->string('bulan');
            $table->string('tahun');
            $table->foreignId('item_id')->constrained('master_data_monthly_payments')->onDelete('cascade');

            $table->enum('tipe_pembayaran', ['bulanan', 'tahunan']);
            $table->enum('status_pembayaran', ['Sudah', 'Belum'])->default('Belum');
            $table->integer('contribution_total');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contribution_monthlies');
    }
};
