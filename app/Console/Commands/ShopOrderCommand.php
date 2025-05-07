<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\V1\Order\Shop\Auth\AdminController;
use App\Models\Common\RequestFilter;
use App\Services\SendPushNotification;
use App\Models\Common\Provider;
use App\Models\Order\StoreOrder;
use App\Models\Service\ServiceRequest;
use Carbon\Carbon;
use Setting;
use DB;


class ShopOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:shoporder'; 

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating the Shop Order';

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
        \Log::info('store Order');

        (new AdminController)->StoreAutoAssign();
        (new AdminController)->StoreTimeOut();
     }   
}
