<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarBrandMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('transport')->create('car_brand_master', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ride_delivery_vehicles_id');
            $table->string('brand_name')->unique();
            $table->string('slug');
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('is_deleted')->default(0);
            $table->integer('created_at');
            $table->integer('updated_at');

            $table->foreign('ride_delivery_vehicles_id')->references('id')->on('ride_delivery_vehicles')
                ->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car_brand_master');
    }
}
