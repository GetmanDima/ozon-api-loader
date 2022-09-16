<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_data_product_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('fin_product_id');

            $table->foreign('fin_product_id')->references('id')->on('financial_data_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_data_product_actions');
    }
};
