<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->create('store_cities', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('admin_service', ['TRANSPORT','ORDER','SERVICE'])->nullable(); 
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('city_id');
            $table->unsignedInteger('company_id');
            $table->tinyInteger('status')->default(1);
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            /*$table->foreign('admin_service_id')->references('id')->on('services')
                ->onUpdate('cascade')->onDelete('cascade');*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_cities');
    }
}
