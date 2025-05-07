<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSmallOrderFeeStoreOrderInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->table('store_order_invoices', function (Blueprint $table) {
            $table->decimal('small_order_fee',10,2)->default(0)->after('wallet_amount');
            $table->decimal('cancellation_fee',10,2)->default(0)->after('small_order_fee');
            $table->decimal('gride_commission_per',10,2)->default(0)->after('commision_amount');
            $table->decimal('gride_commission_amount',10,2)->default(0)->after('gride_commission_per');
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
