<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeleteColumnInRidePeakHours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('common')->table('peak_hours', function (Blueprint $table) {
            $table->tinyInteger('is_deleted')->default(0)->comment('0 - Not Deleted, 1 - Deleted')->after('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('ride_peak_hours', function (Blueprint $table) {
        //     //
        // });
    }
}
