<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRideOnewayCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ride_oneway_cities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ride_city_prices_id')->nullable();
            $table->unsignedInteger('city_id');
            $table->unsignedInteger('source_city_id');
            $table->tinyInteger('status')->default(1)->comment('0-disabled for history requests, 1-active');
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ride_oneway_cities');
    }
}
