<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDatatypeOfleaveatdoorInStoreOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::connection('order')->table('store_orders', function (Blueprint $table) {
        //     $table->string('leave_at_door')->default(0)->change();
        // });
        DB::connection('order')->statement("ALTER TABLE `store_orders` CHANGE `leave_at_door` `leave_at_door` VARCHAR(50) NOT NULL DEFAULT '0'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('store_orders', function (Blueprint $table) {
        //     //
        // });
    }
}
