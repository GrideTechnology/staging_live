<?php

use Illuminate\Database\Seeder;

class StoreTimingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = 1)
    {
        Schema::connection('order')->disableForeignKeyConstraints();

        $Thalapakatti  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Thalapakatti')->first()->id;
        $KFC_Demo  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','KFC Demo')->first()->id;
        $Lassi_Shop  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Lassi Shop')->first()->id;
        $SeaShell  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','SeaShell')->first()->id;
        $AlbertSons  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','AlbertSons')->first()->id;
        $Aldi  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Aldi')->first()->id;
        $Kroger_Shop  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Kroger Shop')->first()->id;
        $_Flower  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','1800 Flower')->first()->id;
        $Chowking_nigeria  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Chowking nigeria')->first()->id;
        $MOS_Burger  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','MOS Burger')->first()->id;
        $Beer_Temple  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Beer Temple')->first()->id;
        $Bonchon_Chicken  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Bonchon Chicken')->first()->id;
        $Marrybrown  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Marrybrown')->first()->id;
        $Metisse_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Metisse Restaurant')->first()->id;
        $Go_Cheers  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Go Cheers')->first()->id;
        $Drinkie  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Drinkie\'Z')->first()->id;
        $Cactus_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Cactus Restaurant')->first()->id;
        $Drankers_Park  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Dranker\'s Park')->first()->id;
        $Liquor_Palace  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Liquor Palace')->first()->id;
        $Jevinik_Restaurant  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Jevinik Restaurant')->first()->id;
        $Ferns_and_Petals  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Ferns and Petals')->first()->id;
        $Fussion_Florist  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Fussion Florist')->first()->id;
        $Just_FlowerZ  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Just FlowerZ')->first()->id;
        $Royal_Blooms  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Royal Blooms')->first()->id;
        $Flora_  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Flora')->first()->id;
        $MooMix  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','MooMix')->first()->id;
        $Walmart  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Walmart')->first()->id;
        $Whole_Foods  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Whole Foods')->first()->id;
        $ShopRite  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','ShopRite')->first()->id;
        $Sala  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Sala')->first()->id;
        $Marketside  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Marketside')->first()->id;
        $Southeastern_Grocers  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Southeastern Grocers')->first()->id;
        $Food_Stuff  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Food Stuff')->first()->id;
        $Hungry_Nation  = DB::connection('order')->table('stores')->where('company_id', $company)->where('store_name','Hungry Nation')->first()->id; 

        

        DB::connection('order')->table('store_timings')->insert([
            ['store_id' => $Aldi,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Kroger_Shop,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $_Flower,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $AlbertSons,'store_start_time' =>'00:00:00','store_end_time' =>'23:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Thalapakatti,'store_start_time' =>'12:00:00','store_end_time' =>'00:10:00','store_day' =>'All','company_id' => $company],
['store_id' => $Chowking_nigeria,'store_start_time' =>'00:00:00','store_end_time' =>'23:59:00','store_day' =>'All','company_id' => $company],
['store_id' => $MOS_Burger,'store_start_time' =>'18:00:00','store_end_time' =>'00:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Bonchon_Chicken,'store_start_time' =>'12:00:00','store_end_time' =>'00:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Marrybrown,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Metisse_Restaurant,'store_start_time' =>'12:00:00','store_end_time' =>'00:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Cactus_Restaurant,'store_start_time' =>'12:00:00','store_end_time' =>'00:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Jevinik_Restaurant,'store_start_time' =>'12:00:00','store_end_time' =>'00:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Ferns_and_Petals,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Walmart,'store_start_time' =>'01:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Fussion_Florist,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Just_FlowerZ,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Flora_,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Royal_Blooms,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $MooMix,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Go_Cheers,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Whole_Foods,'store_start_time' =>'01:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Drankers_Park,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Drinkie,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Liquor_Palace,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Beer_Temple,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $ShopRite,'store_start_time' =>'01:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Sala,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Marketside,'store_start_time' =>'00:00:00','store_end_time' =>'00:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Lassi_Shop,'store_start_time' =>'00:00:00','store_end_time' =>'23:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Southeastern_Grocers,'store_start_time' =>'01:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $KFC_Demo,'store_start_time' =>'00:00:00','store_end_time' =>'23:59:00','store_day' =>'All','company_id' => $company],
['store_id' => $SeaShell,'store_start_time' =>'00:00:00','store_end_time' =>'12:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Food_Stuff,'store_start_time' =>'00:00:00','store_end_time' =>'00:00:00','store_day' =>'All','company_id' => $company],
['store_id' => $Hungry_Nation,'store_start_time' =>'08:00:00','store_end_time' =>'16:00:00','store_day' =>'SUN','company_id' => $company],
['store_id' => $Hungry_Nation,'store_start_time' =>'08:00:00','store_end_time' =>'16:00:00','store_day' =>'MON','company_id' => $company],
['store_id' => $Hungry_Nation,'store_start_time' =>'08:00:00','store_end_time' =>'16:00:00','store_day' =>'TUE','company_id' => $company],
['store_id' => $Hungry_Nation,'store_start_time' =>'08:00:00','store_end_time' =>'16:00:00','store_day' =>'WED','company_id' => $company],
['store_id' => $Hungry_Nation,'store_start_time' =>'08:00:00','store_end_time' =>'16:00:00','store_day' =>'THU','company_id' => $company],
['store_id' => $Hungry_Nation,'store_start_time' =>'08:00:00','store_end_time' =>'16:00:00','store_day' =>'FRI','company_id' => $company],
['store_id' => $Hungry_Nation,'store_start_time' =>'08:00:00','store_end_time' =>'16:00:00','store_day' =>'SAT','company_id' => $company]
        ]);
        
        Schema::connection('order')->enableForeignKeyConstraints();
    }
}
