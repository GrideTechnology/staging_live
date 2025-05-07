<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceColumnInStoreCityPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->table('store_city_prices', function (Blueprint $table) {
            $table->decimal('minimum_delivery_charge',10,2)->default(0)->after('delivery_charge');
            $table->decimal('maximum_delivery_charge',10,2)->default(0)->after('minimum_delivery_charge');
            $table->decimal('minimum_surge_delivery_charge',10,2)->default(0)->after('maximum_delivery_charge');
            $table->decimal('maximum_surge_delivery_charge',10,2)->default(0)->after('minimum_surge_delivery_charge');
            $table->decimal('service_fee',10,2)->default(0)->after('maximum_surge_delivery_charge');
            $table->decimal('cancellation_fee',10,2)->default(0)->after('service_fee');
            $table->integer('commission_fee')->default(0)->after('cancellation_fee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_city_price', function (Blueprint $table) {
            //
        });
    }
}
