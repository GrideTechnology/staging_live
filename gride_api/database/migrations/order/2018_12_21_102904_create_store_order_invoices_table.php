<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreOrderInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->create('store_order_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_order_id');
            $table->string('payment_mode')->nullable();
            $table->string('payment_id')->nullable();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('store_id');
            $table->float('item_price',10,2)->default(0);
            $table->decimal('gross', 10, 2)->default(0);
            $table->decimal('net', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('promocode_id', 10, 2)->default(0);
            $table->decimal('promocode_amount', 10, 2)->default(0);
            $table->decimal('wallet_amount', 10, 2)->default(0);
            $table->decimal('tax_per', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('commision_per', 10, 2)->default(0);
            $table->decimal('commision_amount', 10, 2)->default(0);
            $table->decimal('delivery_per', 10, 2)->default(0);
            $table->decimal('delivery_amount', 10, 2)->default(0);
            $table->decimal('store_package_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('cash', 10, 2)->default(0);
            $table->decimal('payable', 10, 2)->default(0);
            $table->text('cart_details')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('store_order_id')->references('id')->on('store_orders')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_order_invoices');
    }
}
