<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKoloAndClyColumnsToLensaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lensa', function (Blueprint $table) {
            $table->string('cly')->nullable()->comment('Kolom cly untuk lensa');
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
            $table->dropColumn('cly');
        });
    }
}
