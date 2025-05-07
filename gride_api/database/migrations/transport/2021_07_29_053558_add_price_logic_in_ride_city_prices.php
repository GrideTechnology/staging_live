<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceLogicInRideCityPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('transport')->table('ride_city_prices', function (Blueprint $table) {
            $table->decimal('default_price',10,2)->default(0)->after('package_name');
            $table->decimal('cancellation_fee',10,2)->default(0)->after('default_price');
            $table->decimal('max_price',10,2)->default(0)->after('cancellation_fee');
            $table->decimal('min_price',10,2)->default(0)->after('max_price');
            $table->decimal('service_fee',10,2)->default(0)->after('min_price');
            $table->decimal('airport_fee',10,2)->default(0)->after('service_fee');
            $table->decimal('scheduled_cancellation_fee',10,2)->default(0)->after('airport_fee');
            $table->decimal('scheduled_minimum_fee',10,2)->default(0)->after('scheduled_cancellation_fee');
            $table->tinyInteger('distance_type')->default(1)->comment('1 - KMs, 2 - Miles')->after('scheduled_minimum_fee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('ride_city_prices', function (Blueprint $table) {
        //     //
        // });
    }
}
