<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPeakPriceStoreOrderInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->table('store_order_invoices', function (Blueprint $table) {
            $table->double('peak_amount',10,2)->default(0)->after('delivery_amount');
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
