<?php

use Illuminate\Database\Seeder;

class StoreAddonTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
        Schema::connection('order')->disableForeignKeyConstraints();

        $Thalapakatti  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Thalapakatti')->first()->id;
        $KFC_Demo  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'KFC Demo')->first()->id;
        $Lassi_Shop  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Lassi Shop')->first()->id;
        $SeaShell  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'SeaShell')->first()->id;
        $AlbertSons  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'AlbertSons')->first()->id;
        $Aldi  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Aldi')->first()->id;
        $Kroger_Shop  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Kroger Shop')->first()->id;
        $_Flower = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', '1800 Flower')->first()->id;
        $Chowking_nigeria  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Chowking nigeria')->first()->id;
        $MOS_Burger  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'MOS Burger')->first()->id;
        $Beer_Temple  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Beer Temple')->first()->id;
        $Bonchon_Chicken  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Bonchon Chicken')->first()->id;
        $Marrybrown  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Marrybrown')->first()->id;
        $Metisse_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Metisse Restaurant')->first()->id;
        $Go_Cheers  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Go Cheers')->first()->id;
        $Drinkie  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Drinkie\'Z')->first()->id;
        $Cactus_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Cactus Restaurant')->first()->id;
        $Drankers_Park  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Dranker\'s Park')->first()->id;
        $Liquor_Palace  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Liquor Palace')->first()->id;
        $Jevinik_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Jevinik Restaurant')->first()->id;
        $Ferns_and_Petals  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Ferns and Petals')->first()->id;
        $Fussion_Florist  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Fussion Florist')->first()->id;
        $Just_FlowerZ  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Just FlowerZ')->first()->id;
        $Royal_Blooms  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Royal Blooms')->first()->id;
        $Flora  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Flora')->first()->id;
        $MooMix  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'MooMix')->first()->id;
        $Walmart  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Walmart')->first()->id;
        $Whole_Foods  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Whole Foods')->first()->id;
        $ShopRite  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'ShopRite')->first()->id;
        $Sala  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Sala')->first()->id;
        $Marketside  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Marketside')->first()->id;
        $Southeastern_Grocers  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Southeastern Grocers')->first()->id;
        $Food_Stuff  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Food Stuff')->first()->id;
        $Hungry_Nation  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name', 'Hungry Nation')->first()->id; 

        


        DB::connection('order')->table('store_addons')->insert([
            ['store_id' => $Thalapakatti, 'addon_name' => 'Onion', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $Thalapakatti, 'addon_name' => 'Sauce', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $Thalapakatti, 'addon_name' => 'Mayonnaise', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $KFC_Demo, 'addon_name' => 'Onion', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $KFC_Demo, 'addon_name' => 'Mayonnaise', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $KFC_Demo, 'addon_name' => 'Sauce', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $SeaShell, 'addon_name' => 'Cheese', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $Lassi_Shop, 'addon_name' => 'Sause', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $MOS_Burger, 'addon_name' => 'Onion', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $MOS_Burger, 'addon_name' => 'Sauce', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $KFC_Demo, 'addon_name' => 'Cheese', 'addon_status' => 1, 'company_id' => $company],
            ['store_id' => $KFC_Demo, 'addon_name' => 'Cheese', 'addon_status' => 1, 'company_id' => $company]
        ]);
        
        Schema::connection('order')->enableForeignKeyConstraints();
    }
}