<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreDisputeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->create('store_order_disputes', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('dispute_type', ['user', 'provider','system']);
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('store_id')->nullable();
            $table->unsignedInteger('store_order_id')->nullable();
            $table->integer('provider_id')->nullable();
            $table->string('dispute_name');
            $table->string('dispute_title')->nullable();
            $table->string('comments')->nullable();
            $table->text('dispute_type_comments')->nullable();
            $table->double('refund_amount',10, 2)->default(0);
            $table->unsignedInteger('company_id'); 
            $table->enum('comments_by', ['user', 'admin','shop']);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->tinyInteger('is_admin')->default(0);
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('store_dispute');
    }
}
