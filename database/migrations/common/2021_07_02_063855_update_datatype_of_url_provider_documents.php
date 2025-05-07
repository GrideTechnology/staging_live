<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDatatypeOfUrlProviderDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::connection('common')->table('provider_documents', function (Blueprint $table) {                        
        //     $table->text('url')->change();
        // });
        DB::connection('common')->statement("ALTER TABLE `provider_documents` CHANGE `url` `url` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
