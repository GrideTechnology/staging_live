<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Carbon\Carbon;
use App\Notifications;
use DB;
use App\ProviderDocument;
use App\Provider;
use Setting;
use App\Services\SendPushNotification;
use App\Traits\Encryptable;

class DocumentExpiryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:docexpiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provider Expiry Document Alert';

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
            
        $date=Carbon::today()->startOfDay()->addDays($day);
        $date=$date->toDateString();
        $Provider = DB::table('provider_documents')
                        ->where('expires_at',$date)
                        ->get();
                        \Log::info($Provider);
             
        if(!empty($Provider)){
            foreach($Provider as $ride){
                $provider=Provider::where('id',$ride->provider_id)
                        ->select('email')->first();

                $provider = $this->cusdecrypt($provider,env('DB_SECRET'));
                     
                $doc=ProviderDocument::where('id',$ride->id)->first()->expires_at;
                
                $doc = date('d-m-Y', strtotime($doc));
               

              //  $provider=[$provider,'kabilan@appoets.com'];
                Notification::send($provider, new ProviderDocumentExpiry($doc));

                // $c = \App\Admin::get();
      
                // $user = Provider::where('id',$ride->provider_id)->first();
              
                // Notifications::send($c, new AdminProviderDocumentExpiry($doc,$user));

                $document = ProviderDocument::where('id',$ride->id)->first();
                $document->status = 'ASSESSING';
                $document->save();

                $provider=Provider::where('id',$ride->provider_id)->first();
                $provider->status='document';
                $provider->expiry_status = 1;
                $provider->save();

                (new SendPushNotification)->DocumentsExpired($ride->provider_id);

            }
        }
    }
}
