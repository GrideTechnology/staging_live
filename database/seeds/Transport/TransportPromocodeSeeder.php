<?php

use Illuminate\Database\Seeder;

class TransportPromocodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
        Schema::disableForeignKeyConstraints();

        DB::table('promocodes')->insert([
            ['company_id' => $company, 'promo_code' => 'Free50', 'service' =>'ORDER', 'picture' => url('/').'/images/common/promocodes/transport.png', 'percentage' => '12.00', 'max_amount' => '10.00', 'promo_description' => '12% off, Max discount is 10', 'expiration' => '2019-11-15', 'status' => 'ADDED']
        ]);
        
        Schema::enableForeignKeyConstraints();
    }
}
