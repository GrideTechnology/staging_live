<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCancellationFeeRideRequestPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('transport')->table('ride_request_payments', function (Blueprint $table) {
            $table->double('cancellation_fee')->default(0)->after('night_fare_amount');
            $table->tinyInteger('cancellation_fee_goesto')->default(0)->comment('0 - Nothing, 1 - Provider(Driver), 2 - Gride')->after('cancellation_fee');
        });
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
