<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWalletBalanceInAdmins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('common')->statement("ALTER TABLE `admin_wallets` CHANGE `transaction_type` `transaction_type` INT(11) NULL DEFAULT NULL COMMENT '1-commission,2-userrecharge,3-tripdebit,4-providerrecharge,5-providersettle,6-fleetrecharge,7-fleetcommission,8-fleetsettle,9-taxcredit,10-discountdebit,11-discountrecharge,12-cancellation charge';");
        // Schema::table('admin_wallet', function (Blueprint $table) {
            
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            //
        });
    }
}
