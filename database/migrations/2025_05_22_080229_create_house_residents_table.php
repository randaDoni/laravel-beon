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
        Schema::create('house_residents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('bulan')->nullable();
            $table->string('tahun')->nullable();
            $table->unsignedBigInteger('resident_id');
            $table->unsignedBigInteger('house_id');
            $table->string('tipe_hunian');
            $table->date('date_of_entry')->nullable();
            $table->date('exit_date')->nullable();

            $table->foreign('resident_id')->references('id')->on('residents')->onDelete('cascade');
            $table->foreign('house_id')->references('id')->on('houses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('house_residents');
    }
};
