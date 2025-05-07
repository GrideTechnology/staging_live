<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('common')->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content')->nullable(true)->useCurrent();
            $table->string('slug');
            $table->timestamp('time');
            $table->string('image');
            $table->string('video')->nullable(true)->useCurrent();
            $table->softDeletes(); // <-- This will add a deleted_at field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
