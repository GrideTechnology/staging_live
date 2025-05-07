<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreCartItemAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->create('store_cart_item_addons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_cart_item_id');
            $table->unsignedInteger('store_item_addons_id');
            $table->unsignedInteger('store_cart_id');
            $table->unsignedInteger('store_addon_id')->nullable();
            $table->decimal('addon_price', 10, 2)->default(0);
            $table->unsignedInteger('company_id');
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('store_cart_item_id')->references('id')->on('store_items')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('store_cart_id')->references('id')->on('store_carts')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('store_item_addons_id')->references('id')->on('store_item_addons')
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
        Schema::dropIfExists('store_cart_item_addons');
    }
}
