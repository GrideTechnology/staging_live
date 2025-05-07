<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPeakPercentRideRequestPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('transport')->table('ride_request_payments', function (Blueprint $table) {
            $table->double('peak_percent',10,2)->default(0)->after('peak_amount');
            $table->double('user_ratio',10,2)->default(0)->after('peak_percent');
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
