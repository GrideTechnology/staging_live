<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->create('stores', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('store_type_id');
            $table->unsignedInteger('company_id');
            $table->string('store_name');
            $table->string('email')->nullable();
            $table->string('password')->nullable(); 
            $table->string('store_location')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->string('store_zipcode')->nullable();
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('city_id');
            $table->unsignedInteger('zone_id');
            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->Integer('country_code');
            $table->string('picture')->nullable();
            $table->string('device_token')->nullable();
            $table->string('device_id')->nullable();
            $table->enum('device_type',array('ANDROID','IOS'))->nullable();
            $table->string('language')->default('en');
            $table->decimal('store_packing_charges', 10, 2)->default(0);
            $table->Integer('store_gst')->default(0);
            $table->Integer('commission')->default(0);
            $table->tinyInteger('is_bankdetail')->default(0);
            $table->string('offer_min_amount');
            $table->unsignedInteger('offer_percent');
            $table->string('estimated_delivery_time');
            $table->decimal('rating', 4, 2)->nullable();
            $table->string('otp')->nullable();
            $table->text('description');
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('free_delivery')->default(0); 
            $table->string('currency')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->double('wallet_balance', 10, 2)->default(0);
            $table->enum('is_veg', ['Pure Veg','Non Veg'])->nullable();
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('store_type_id')->references('id')->on('store_types')
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
        Schema::dropIfExists('stores');
    }
}

