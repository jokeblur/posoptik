<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomOrderAndSalesToLensaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lensa', function (Blueprint $table) {
            $table->boolean('is_custom_order')->default(false)->comment('False = Ready Stock, True = Custom Order');
            $table->unsignedInteger('sales_id')->nullable()->comment('ID Sales yang handle order ini');
            
            // Add foreign key constraint
            $table->foreign('sales_id')->references('id_sales')->on('sales')->onDelete('set null');
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
            $table->dropForeign(['sales_id']);
            $table->dropColumn(['is_custom_order', 'sales_id']);
        });
    }
} 