<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeleteColumnInCuisines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('order')->table('cuisines', function (Blueprint $table) {
            $table->tinyInteger('is_deleted')->default(0)->comment('0 - Not Deleted, 1 - Deleted')->after('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('cuisines', function (Blueprint $table) {
        //     //
        // });
    }
}
