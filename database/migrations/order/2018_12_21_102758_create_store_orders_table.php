<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->create('store_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('admin_service', ['TRANSPORT','ORDER','SERVICE'])->nullable(); 
            $table->string('store_order_invoice_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('user_address_id')->nullable();
            $table->unsignedInteger('promocode_id')->nullable();
            $table->unsignedInteger('store_id')->nullable();
            $table->unsignedInteger('store_type_id')->nullable();
            $table->unsignedInteger('provider_id')->nullable();
            $table->unsignedInteger('provider_vehicle_id')->nullable();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('city_id');
            $table->unsignedInteger('country_id');
            $table->string('note')->nullable();
            $table->text('description')->nullable();
            $table->longText('route_key')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->timestamp('schedule_datetime')->nullable();
            $table->text('pickup_address')->nullable();
            $table->text('delivery_address')->nullable();
            $table->enum('order_type', ['DELIVERY','TAKEAWAY'])->default('DELIVERY');
            $table->string('order_otp')->nullable();
            $table->unsignedInteger('order_ready_time')->nullable();
            $table->integer('order_ready_status')->nullable();
            $table->integer('paid')->default(0);
            $table->tinyInteger('user_rated')->default(0);
            $table->tinyInteger('provider_rated')->default(0);
            $table->enum('cancelled_by', ['NONE','USER','PROVIDER'])->nullable();
            $table->string('cancel_reason')->nullable();
            $table->string('currency')->nullable();
            $table->enum('status', ['ORDERED','RECEIVED','STORECANCELLED','PROVIDEREJECTED','CANCELLED','SEARCHING','PROCESSING','STARTED','REACHED','PICKEDUP','ARRIVED','DELIVERED','COMPLETED'])->comment('ORDERED - User creates the order, RECEIVED - Shop receives the order, STORECANCELLED - Store cancel the order ()'); 
            $table->integer('schedule_status')->default(0);
            $table->timestamp('assigned_at')->nullable();
            $table->string('timezone')->nullable();
            $table->enum('request_type', ['AUTO','MANUAL'])->default('AUTO');
            $table->tinyInteger('leave_at_door')->default(0);
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('store_id')->references('id')->on('stores')
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
        Schema::dropIfExists('store_orders');
    }
}
