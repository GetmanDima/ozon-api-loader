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
        Schema::create('financial_data_products', function (Blueprint $table) {
            $table->id();
            $table->string('client_price');
            $table->float('commission_amount');
            $table->float('commission_percent');
            $table->float('old_price');
            $table->float('payout');
            $table->float('price');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->float('total_discount_percent');
            $table->float('total_discount_value');
            $table->unsignedBigInteger('fbo_id');

            $table->foreign('fbo_id')->references('id')->on('fbos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_data_products');
    }
};
