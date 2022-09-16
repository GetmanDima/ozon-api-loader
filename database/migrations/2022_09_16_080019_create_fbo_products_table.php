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
        Schema::create('fbo_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sku');
            $table->string('name');
            $table->integer('quantity');
            $table->string('offer_id');
            $table->string('price');
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
        Schema::dropIfExists('fbo_products');
    }
};
