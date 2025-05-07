<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStateIdRideCityPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('transport')->table('ride_city_prices', function (Blueprint $table) {
            $table->integer('state_id')->unsigned()->nullable()->after('geofence_id');                        
        });

        DB::connection('transport')->statement("ALTER TABLE `ride_city_prices` CHANGE `city_id` `city_id` INT(10) UNSIGNED NULL DEFAULT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
