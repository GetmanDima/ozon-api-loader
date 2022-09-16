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
        Schema::create('product_last_ids', function (Blueprint $table) {
            $table->id();
            $table->string("last_id");
            $table->unsignedBigInteger("client_id");
            $table->timestamp('created_at')->useCurrent();

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
        Schema::dropIfExists('product_last_ids');
    }
};
