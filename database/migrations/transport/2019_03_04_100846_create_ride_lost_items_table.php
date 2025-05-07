<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRideLostItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('transport')->create('ride_lost_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ride_request_id');
            $table->unsignedInteger('company_id');
            $table->integer('user_id');            
            $table->string('lost_item_name');
            $table->string('comments')->nullable();
            $table->enum('comments_by', ['user', 'admin']);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->tinyInteger('is_admin')->default(0);
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ride_lost_items');
    }
}
