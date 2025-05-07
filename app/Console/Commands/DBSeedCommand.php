<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Command;
use App\Models\Common\Company;
use Carbon\Carbon;
use DB;

class DBSeedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'company:seed';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Seed data to company';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		if ($this->confirm('Do you wish to clear existing data?')) {

			Schema::disableForeignKeyConstraints();

			DB::table('admin_services')->truncate();
			DB::table('settings')->truncate();
			DB::table('admins')->truncate();
			DB::table('company_countries')->truncate();
			DB::table('company_cities')->truncate();
			DB::table('country_bank_forms')->truncate();
			DB::table('company_city_admin_services')->truncate();
			DB::table('users')->truncate();
			DB::table('providers')->truncate();
			DB::table('provider_vehicles')->truncate();
			DB::table('cms_pages')->truncate();
			DB::table('menus')->truncate();
			DB::table('menu_cities')->truncate();
			DB::table('provider_services')->truncate();
			DB::table('documents')->truncate();
			DB::table('disputes')->truncate();
			DB::table('disputes')->truncate();
			DB::table('reasons')->truncate();
			DB::table('promocodes')->truncate();

			DB::connection('transport')->table('ride_types')->truncate();
			DB::connection('transport')->table('ride_delivery_vehicles')->truncate();
			DB::connection('transport')->table('ride_cities')->truncate();
			DB::connection('transport')->table('ride_city_prices')->truncate();

			DB::connection('service')->table('service_cities')->delete();
			DB::connection('service')->statement('ALTER TABLE service_cities AUTO_INCREMENT = 1;');
			DB::connection('service')->table('service_city_prices')->truncate();
			DB::connection('service')->table('services')->delete();
			DB::connection('service')->statement('ALTER TABLE services AUTO_INCREMENT = 1;');
			DB::connection('service')->table('service_categories')->delete();
			DB::connection('service')->statement('ALTER TABLE service_categories AUTO_INCREMENT = 1;');
			DB::connection('service')->table('service_subcategories')->delete();
			DB::connection('service')->statement('ALTER TABLE service_subcategories AUTO_INCREMENT = 1;');

			DB::connection('order')->table('store_types')->delete();
			DB::connection('order')->statement('ALTER TABLE store_types AUTO_INCREMENT = 1;');
			DB::connection('order')->table('store_timings')->delete();
			DB::connection('order')->statement('ALTER TABLE store_timings AUTO_INCREMENT = 1;');
			DB::connection('order')->table('store_categories')->delete();
			DB::connection('order')->statement('ALTER TABLE store_categories AUTO_INCREMENT = 1;');

			DB::connection('order')->table('store_cuisines')->truncate();
			DB::connection('order')->table('store_timings')->truncate();
			DB::connection('order')->table('store_categories')->delete();
			DB::connection('order')->statement('ALTER TABLE store_categories AUTO_INCREMENT = 1;');

			DB::connection('order')->table('store_addons')->delete();
			DB::connection('order')->statement('ALTER TABLE store_addons AUTO_INCREMENT = 1;');

			DB::connection('order')->table('store_items')->delete();
			DB::connection('order')->statement('ALTER TABLE store_items AUTO_INCREMENT = 1;');
			DB::connection('order')->table('store_item_addons')->delete();
			DB::connection('order')->statement('ALTER TABLE store_item_addons AUTO_INCREMENT = 1;');

			DB::connection('order')->table('store_cities')->truncate();
			DB::connection('order')->table('store_city_prices')->truncate();

			Schema::enableForeignKeyConstraints();

		}

		$company_name = $this->ask('Enter your company name');

		$existing_company = DB::table('companies')->where('company_name', $company_name)->first();

		if($existing_company != null) {
			$this->error('Company already exists!');
		} else {
			if ($this->confirm('Do you wish to continue?')) {
				$company = Company::create([
					'company_name' => $company_name,
					'domain' => '127.0.0.1',
					'base_url' => 'http://127.0.0.1:8001/api/v1',
					'socket_url' => 'http://127.0.0.1:8990',
					'access_key' => '123456',
					'expiry_date' => Carbon::now()->addYear()
				]);



				(new \AdminServiceTableSeeder())->run($company->id);
				(new \SettingsTableSeeder())->run($company->id);
				(new \AdminTableSeeder())->run($company->id);
				(new \CompanyCityTableSeeder())->run($company->id);
				(new \DemoTableSeeder())->run($company->id);
				(new \ProviderVehicleSeeder())->run($company->id);
				(new \CmsPageSeeder())->run($company->id);
				(new \DocumentSeeder())->run($company->id);

				(new \TransportTableSeeder())->run($company->id);
				(new \TransportDisputeSeeder())->run($company->id);
				(new \TransportDocumentSeeder())->run($company->id);
				(new \TransportReasonSeeder())->run($company->id);
				(new \TransportPromocodeSeeder())->run($company->id);

				(new \ServiceCategoryTableSeeder())->run($company->id);
				(new \ServiceSubCategoriesSeeder())->run($company->id);
				(new \ServiceDisputeSeeder())->run($company->id);
				(new \ServiceReasonSeeder())->run($company->id);
				(new \ServiceTableSeeder())->run($company->id);
				(new \ServiceSeeder())->run($company->id);
				(new \ServicePromocodeSeeder())->run($company->id);

				(new \OrderDisputeSeeder())->run($company->id);
				(new \OrderReasonSeeder())->run($company->id);
				(new \StoreTypeSeeder())->run($company->id);
				(new \CuisineTableSeeder())->run($company->id);
				(new \StoreTableSeeder())->run($company->id);
				(new \OrderPromocodeSeeder())->run($company->id);
				(new \StoreAddonTableSeeder())->run($company->id);
				(new \StoreCuisineTableSeeder())->run($company->id);
				(new \StoreTimingTableSeeder())->run($company->id);
				(new \StoreCategoryTableSeeder())->run($company->id);
				(new \StoreItemTableSeeder())->run($company->id);
				(new \StoreItemAddonTableSeeder())->run($company->id);
				(new \OrderTableSeeder())->run($company->id);

				$this->info('Seed Data completed');
			}
		}
		
	}
}
