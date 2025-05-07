<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFemaleInRideTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('transport')->table('ride_types', function (Blueprint $table) {
            $table->tinyInteger('is_female')->default(0)->after('ride_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('ride_types', function (Blueprint $table) {
        //     //
        // });
    }
}
