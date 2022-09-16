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
        Schema::create('fbos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cancel_reason_id');
            $table->dateTime('created_at', 3);
            $table->dateTime('in_process_at', 3);
            $table->unsignedBigInteger('order_id');
            $table->string('order_number');
            $table->string('posting_number');
            $table->string('status');
            $table->unsignedBigInteger('client_id');

            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fbos');
    }
};
