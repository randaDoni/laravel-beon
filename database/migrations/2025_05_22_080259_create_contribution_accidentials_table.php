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
        Schema::create('contribution_accidentials', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('contribution_name');
            $table->date('date');
            $table->string('payment_type');
            $table->enum('payment_status',['complete','half','not yet']);
            $table->integer('contribution_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contribution_accidentials');
    }
};
