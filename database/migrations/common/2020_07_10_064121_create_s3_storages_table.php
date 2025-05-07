<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateS3StoragesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s3_storages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('request_id');
            $table->string('img_url');
            $table->enum('type', ['USER','PROVIDER']);
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('s3_storages');
    }
}
