<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('open_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->date('tanggal');
            $table->boolean('is_open')->default(false);
            $table->timestamps();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->unique(['branch_id', 'tanggal']);
        });
    }
    public function down()
    {
        Schema::dropIfExists('open_days');
    }
}; 