<?php

use Illuminate\Database\Seeder;

class TransportDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
        Schema::disableForeignKeyConstraints();

        DB::table('documents')->insert([
            ['company_id' => $company, 'service' => 'TRANSPORT','name' =>'License','type' =>'TRANSPORT','file_type' =>'image','is_backside' =>1,'is_expire' =>1,'status' =>1]        
        ]);
        
        Schema::enableForeignKeyConstraints();
    }
}
