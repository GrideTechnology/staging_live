<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->create('store_carts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('store_item_id');
            $table->unsignedInteger('store_id');
            $table->unsignedInteger('store_order_id')->nullable();
            $table->unsignedInteger('company_id');
            $table->float('quantity', 8, 2)->default(0);
            $table->float('item_price', 8, 2)->default(0);
            $table->float('total_item_price', 8, 2)->default(0);
            $table->float('tot_addon_price', 8, 2)->default(0);
            $table->text('note')->nullable();
            $table->longText('product_data')->nullable();
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('store_item_id')->references('id')->on('store_items')
                ->onUpdate('cascade')->onDelete('cascade');

            // $table->foreign('store_order_id')->references('id')->on('store_orders')
            //     ->onUpdate('cascade')->onDelete('cascade');

        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_carts');
    }
}
