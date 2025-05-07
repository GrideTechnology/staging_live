<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRideCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('transport')->create('ride_cities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('city_id');
            $table->enum('admin_service', ['TRANSPORT','ORDER','SERVICE'])->nullable(); 
            $table->unsignedInteger('ride_category_id')->nullable();
            $table->unsignedInteger('vehicle_service_id')->nullable();
            $table->unsignedInteger('company_id');
            $table->decimal('comission', 10, 2)->default(0);
            $table->decimal('fleet_comission', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('surge', 10, 2)->default(0);
            $table->decimal('night_charges', 10, 2)->default(0);
            $table->decimal('driver_beta_amount', 10, 2)->default(0);
            $table->decimal('waiting_percentage', 10, 2)->default(0);
            $table->decimal('peak_percentage', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->nullable();
            $table->decimal('fleet_commission', 10, 2)->nullable();
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ride_category_id')->references('id')->on('ride_categories')
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
        Schema::dropIfExists('ride_cities');
    }
}
