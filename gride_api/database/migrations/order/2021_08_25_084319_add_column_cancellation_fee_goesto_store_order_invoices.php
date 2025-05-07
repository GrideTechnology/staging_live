<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCancellationFeeGoestoStoreOrderInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->table('store_order_invoices', function (Blueprint $table) {
            $table->tinyInteger('cancellation_fee_goesto')->default(0)->comment('0-Nothing,1-Provider(Delivery Boy),2-Gride')->after('cancellation_fee');
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
