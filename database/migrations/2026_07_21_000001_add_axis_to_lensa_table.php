<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAxisToLensaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lensa', function (Blueprint $table) {
            $table->string('axis')->nullable()->comment('Ukuran axis lensa gosok');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lensa', function (Blueprint $table) {
            $table->dropColumn('axis');
        });
    }
}
