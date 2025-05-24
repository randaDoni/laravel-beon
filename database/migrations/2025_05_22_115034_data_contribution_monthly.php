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
        Schema::create('data_contribution_monthly', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained();
            $table->string('bulan');
            $table->integer('tahun');
            $table->foreignId('item_id')->constrained('master_data_monthly_payments');
            $table->enum('tipe_pembayaran', ['bulanan', 'tahunan']);
            $table->enum('status_pembayaran', ['Sudah', 'Belum'])->default('Belum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
