<?php

use App\Models\Common\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateBaseUrl extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        // DB::statement('UPDATE menus set icon = replace(icon, "localhost", "api.gridetech.com");');
        // echo '<pre>';print_r('test');exit;
        // $menus = Menu::all();
        // echo '<pre>';print_r($menus);exit;
        // foreach($menus as $menu){                        
        //     $menu->icon = str_replace("localhost","api.gridetech.com",$menu);
        //     echo '<pre>';print_r($menu->icon);exit;
        //     $menu->save();
        // }
        // DB::statement("UPDATE admin_services SET base_url = 'https://api.gridetech.com/api/v1' WHERE id = 1");
        // DB::statement("UPDATE admin_services SET base_url = 'https://api.gridetech.com/api/v1' WHERE id = 2");
        // DB::statement("UPDATE admin_services SET base_url = 'https://api.gridetech.com/api/v1' WHERE id = 3");        

        // DB::statement("UPDATE companies SET base_url = 'https://api.gridetech.com/api/v1' WHERE id = 1");
        // DB::statement("UPDATE companies SET domain = 'gridetech.com' WHERE id = 1");
        

        // DB::statement("UPDATE admin_services SET base_url = 'http://52.1.119.62/grideapp_api/public/api/v1' WHERE id = 1");
        // DB::statement("UPDATE admin_services SET base_url = 'http://52.1.119.62/grideapp_api/public/api/v1' WHERE id = 2");
        // DB::statement("UPDATE admin_services SET base_url = 'http://52.1.119.62/grideapp_api/public/api/v1' WHERE id = 3");        

        // DB::statement("UPDATE companies SET base_url = 'http://52.1.119.62/grideapp_api/public/api/v1' WHERE id = 1");
        // DB::statement("UPDATE companies SET domain = '52.1.119.62' WHERE id = 1");
        
        // DB::statement("UPDATE admin_services SET base_url = 'http://dev2.spaceo.in/project/grideapp_web/public/api/v1' WHERE id = 1");
        // DB::statement("UPDATE admin_services SET base_url = 'http://dev2.spaceo.in/project/grideapp_web/public/api/v1' WHERE id = 2");
        // DB::statement("UPDATE admin_services SET base_url = 'http://dev2.spaceo.in/project/grideapp_web/public/api/v1' WHERE id = 3");
        // DB::statement("UPDATE admin_services SET base_url = 'http://3.215.248.237/grideapp_api/public/api/v1' WHERE id = 1");
        // DB::statement("UPDATE admin_services SET base_url = 'http://3.215.248.237/grideapp_api/public/api/v1' WHERE id = 2");
        // DB::statement("UPDATE admin_services SET base_url = 'http://3.215.248.237/grideapp_api/public/api/v1' WHERE id = 3");


        // DB::statement("UPDATE admin_services SET base_url = 'http://3.215.248.237/grideapp_api/public/api/v1' WHERE id = 1");
        // DB::statement("UPDATE admin_services SET base_url = 'http://3.215.248.237/grideapp_api/public/api/v1' WHERE id = 2");
        // DB::statement("UPDATE admin_services SET base_url = 'http://3.215.248.237/grideapp_api/public/api/v1' WHERE id = 3");

        // DB::statement("UPDATE companies SET base_url = 'http://3.215.248.237/grideapp_api/public/api/v1' WHERE id = 1");
        // DB::statement("UPDATE companies SET domain = '3.215.248.237' WHERE id = 1");

        // DB::statement("UPDATE companies SET base_url = 'http://dev6.spaceo.in/project/grideapp_web/public/api/v1' WHERE id = 1");
        // DB::statement("UPDATE companies SET domain = 'dev2.spaceo.in' WHERE id = 1");
        // DB::statement("UPDATE companies SET expiry_date = '2023-12-31 06:33:17' WHERE id = 1");
    }
}
