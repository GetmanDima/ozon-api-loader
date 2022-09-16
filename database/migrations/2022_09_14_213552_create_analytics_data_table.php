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
        Schema::create('analytics_data', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('delivery_type');
            $table->boolean('is_premium');
            $table->string('payment_type_group_name');
            $table->string('region');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('warehouse_name');
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
        Schema::dropIfExists('analytics_data');
    }
};
