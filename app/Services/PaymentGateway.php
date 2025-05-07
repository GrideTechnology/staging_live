<?php 

namespace App\Services;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Validator;
use Exception;
use DateTime;
use Auth;
use Lang;
use App\Models\Common\Setting;
use App\ServiceType;
use App\Models\Common\Promocode;
use App\Provider;
use App\ProviderService;
use App\Helpers\Helper;
use GuzzleHttp\Client;
use App\Models\Common\PaymentLog;
use Illuminate\Support\Facades\Log;
//PayuMoney
use Tzsk\Payu\Facade\Payment AS PayuPayment;

//Paypal
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payee;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

use Redirect;
use Session;
use URL;


class PaymentGateway {

	private $gateway;

	public function __construct($gateway){
		$this->gateway = strtoupper($gateway);
	}

	public function process($attributes) {
		$provider_url = '';

		$gateway = ($this->gateway == 'STRIPE') ? 'CARD' : $this->gateway;
		\Log::info('getway');
		\Log::info($gateway);
		\Log::info($attributes['order']);
		// echo '<pre>';print_r($attributes['order']);
		// echo '<pre>';print_r($gateway);
		$log = PaymentLog::where('transaction_code', $attributes['order'])->where('payment_mode', $gateway)->first();
		// echo '<pre>';print_r();exit;
		// \Log::info('log');
		// \Log::info($log);
		// echo $log->user_type;exit;
		// if($log->user_type == 'provider') {		
		// 	$provider_url = '/provider';
		// }
		
		switch ($this->gateway) {

			case "STRIPE":

				try {
					
				
					$settings = json_decode(json_encode(Setting::first()->settings_data));
        			$paymentConfig = json_decode( json_encode( $settings->payment ) , true);

        			$cardObject = array_values(array_filter( $paymentConfig, function ($e) { return $e['name'] == 'card'; }));
			        $card = 0;

			        $stripe_secret_key = "";
			        $stripe_publishable_key = "";
			        $stripe_currency = "";

			        if(count($cardObject) > 0) { 
			            $card = $cardObject[0]['status'];

			            $stripeSecretObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_secret_key'; }));
			            $stripePublishableObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_publishable_key'; }));
			            $stripeCurrencyObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_currency'; }));

			            if(count($stripeSecretObject) > 0) {
			                $stripe_secret_key = $stripeSecretObject[0]['value'];
			            }

			            if(count($stripePublishableObject) > 0) {
			                $stripe_publishable_key = $stripePublishableObject[0]['value'];
			            }

			            if(count($stripeCurrencyObject) > 0) {
			                $stripe_currency = $stripeCurrencyObject[0]['value'];
			            }
			        }

					
        			\Stripe\Stripe::setApiKey( $stripe_secret_key );					
					  $Charge = \Stripe\Charge::create([
		                "amount" => $attributes['amount'] * 100,
		                "currency" => $attributes['currency'],
		                "customer" => $attributes['customer'],
		                "card" => $attributes['card'],
		                "description" => $attributes['description'],
		                "receipt_email" => $attributes['receipt_email']
		             ]);
					 
					$log->response = json_encode($Charge);
                	$log->save();					
					$paymentId = $Charge['id'];

					return (Object)['status' => 'SUCCESS', 'payment_id' => $paymentId];

				} catch(StripeInvalidRequestError $e){					
					$res = (Object)['status' => 'FAILURE', 'message' => $e->getMessage().' '.$e->getFile().' at line '.$e->getLine()];
					Log::warning(json_encode($res));
					return $res;

	            } catch(Exception $e) {					
					// echo '<pre>';print_r($e);exit;
	                $res = (Object)['status' => 'FAILURE','message' => $e->getMessage().' '.$e->getFile().' at line '.$e->getLine()];
					Log::warning(json_encode($res));
					return $res;
	            }

				break;

			default:
				return redirect('dashboard');
		}
		

	}


	function CreatePaymentIntent($amount){
		try {
			$settings = json_decode(json_encode(Setting::first()->settings_data));
			$paymentConfig = json_decode( json_encode( $settings->payment ) , true);

			$cardObject = array_values(array_filter( $paymentConfig, function ($e) { return $e['name'] == 'card'; }));
	        $card = 0;

	        $stripe_secret_key = "";
	        $stripe_publishable_key = "";
	        $stripe_currency = "";

	        if(count($cardObject) > 0) { 
	            $card = $cardObject[0]['status'];

	            $stripeSecretObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_secret_key'; }));
	            $stripePublishableObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_publishable_key'; }));
	            $stripeCurrencyObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_currency'; }));

	            if(count($stripeSecretObject) > 0) {
	                $stripe_secret_key = $stripeSecretObject[0]['value'];
	            }

	            if(count($stripePublishableObject) > 0) {
	                $stripe_publishable_key = $stripePublishableObject[0]['value'];
	            }

	            if(count($stripeCurrencyObject) > 0) {
	                $stripe_currency = $stripeCurrencyObject[0]['value'];
	            }
	        }

	        $stripe = new \Stripe\StripeClient($stripe_secret_key);

	        if(Auth::guard('user')->user()){
	        	if(Auth::guard('user')->user()->stripe_cust_id!=''){
		        	$customer_id = Auth::guard('user')->user()->stripe_cust_id;
		        }else{
		        	$customer = $stripe->customers->create([
					  'name' => Auth::guard('user')->user()->first_name.' '.Auth::guard('user')->user()->last_name,
					  'email' => Auth::guard('user')->user()->email,
					]);

		        	$customer_id = $customer->id;
		        }
	        }
			if(Auth::guard('provider')->user()){
				if(Auth::guard('provider')->user()->stripe_cust_id!=''){
					$customer_id = Auth::guard('provider')->user()->stripe_cust_id;
				}else{
		        	$customer = $stripe->customers->create([
					  'name' => Auth::guard('provider')->user()->first_name.' '.Auth::guard('provider')->user()->last_name,
					  'email' => Auth::guard('provider')->user()->email,
					]);

		        	$customer_id = $customer->id;
		        }
	        }

			
			$paymentIntent =$stripe->paymentIntents->create([
							        'amount' => $amount,
							        'currency' => 'usd',
							        // In the latest version of the API, specifying the `automatic_payment_methods` parameter is optional because Stripe enables its functionality by default.
							        'automatic_payment_methods' => [
							            'enabled' => true,
							        ],
							        'customer' => $customer_id
							    ]);
			//print_r($paymentIntent);exit;
			return $paymentIntent;

		} catch(StripeInvalidRequestError $e){					
					$res = (Object)['status' => 'FAILURE', 'message' => $e->getMessage().' '.$e->getFile().' at line '.$e->getLine()];
					Log::warning(json_encode($res));
					return $res;

		            } catch(Exception $e) {					
						// echo '<pre>';print_r($e);exit;
		                $res = (Object)['status' => 'FAILURE','message' => $e->getMessage().' '.$e->getFile().' at line '.$e->getLine()];
						Log::warning(json_encode($res));
						return $res;
		            }
	}


	function CreateProviderAccount(){
		$settings = json_decode(json_encode(Setting::first()->settings_data));
		$paymentConfig = json_decode( json_encode( $settings->payment ) , true);

		$cardObject = array_values(array_filter( $paymentConfig, function ($e) { return $e['name'] == 'card'; }));
        $card = 0;

        $stripe_secret_key = "";
        $stripe_publishable_key = "";
        $stripe_currency = "";

        if(count($cardObject) > 0) { 
            $card = $cardObject[0]['status'];

            $stripeSecretObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_secret_key'; }));
            $stripePublishableObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_publishable_key'; }));
            $stripeCurrencyObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_currency'; }));

            if(count($stripeSecretObject) > 0) {
                $stripe_secret_key = $stripeSecretObject[0]['value'];
            }

            if(count($stripePublishableObject) > 0) {
                $stripe_publishable_key = $stripePublishableObject[0]['value'];
            }

            if(count($stripeCurrencyObject) > 0) {
                $stripe_currency = $stripeCurrencyObject[0]['value'];
            }
        }


        $stripe = new \Stripe\StripeClient();

        if(Auth::guard('provider')->user()){
        	$provider = Provider::find(Auth::guard('provider')->user()->id);
			if(Auth::guard('provider')->user()->stripe_acct_id==''){
	        	$account = $stripe->accounts->create(['type' => 'express']);
	        	$provider->stripe_acct_id = $account->id;
				$provider->save();

				//Live Account: acct_1P4OAH2cffg1Lwod
				//Test Account: acct_1P4iRNGfa6Hi2GZB
				$accountlinks = $stripe->accountLinks->create([
				  'account' => $provider->stripe_acct_id,
				  'refresh_url' => 'https://gridetech.com/reauth',
				  'return_url' => 'https://gridetech.com/return',
				  'type' => 'account_onboarding',
				]);

				
	        }else{
	        	
	        	$accountlinks = $stripe->accountLinks->create([
				  'account' => $provider->stripe_acct_id,
				  'refresh_url' => 'https://gridetech.com/reauth',
				  'return_url' => 'https://gridetech.com/return',
				  'type' => 'account_onboarding',
				]);

	        }

	        return $accountlinks;
        }
		

        //Create Price id
        // $price = $stripe->prices->create([
		// 	  'currency' => 'usd',
		// 	  'unit_amount' => 100,
		// 	  'product_data' => ['name' => 'Gold Plan'],
		// 	]);

        //Test Price ID: price_1P4j3lGg2FEMhsYQfpFBlUyl
        //return $price;
		// $transfer = $stripe->transfers->create([
		// 	  'amount' => 100,
		// 	  'currency' => 'usd',
		// 	  'destination' => 'acct_1P4iRNGfa6Hi2GZB',
		// 	  'transfer_group' => 'ORDER_95',
		// 	]);
        // return $transfer;

        //Balance Retrieve
		// $balance = $stripe->balance->retrieve([], ['stripe_account' => 'acct_1P4iRNGfa6Hi2GZB']);
		// return $balance;
	}

	function TransferPayment($request){
		$settings = json_decode(json_encode(Setting::first()->settings_data));
		$paymentConfig = json_decode( json_encode( $settings->payment ) , true);

		$cardObject = array_values(array_filter( $paymentConfig, function ($e) { return $e['name'] == 'card'; }));
        $card = 0;

        $stripe_secret_key = "";
        $stripe_publishable_key = "";
        $stripe_currency = "";

        if(count($cardObject) > 0) { 
            $card = $cardObject[0]['status'];

            $stripeSecretObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_secret_key'; }));
            $stripePublishableObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_publishable_key'; }));
            $stripeCurrencyObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_currency'; }));

            if(count($stripeSecretObject) > 0) {
                $stripe_secret_key = $stripeSecretObject[0]['value'];
            }

            if(count($stripePublishableObject) > 0) {
                $stripe_publishable_key = $stripePublishableObject[0]['value'];
            }

            if(count($stripeCurrencyObject) > 0) {
                $stripe_currency = $stripeCurrencyObject[0]['value'];
            }
        }
        
        //$stripe = new \Stripe\StripeClient($stripe_secret_key);
        
        $stripe = new \Stripe\StripeClient();

		if(Auth::guard('provider')->user()){
			if(Auth::guard('provider')->user()->stripe_acct_id==''){
				return Helper::getResponse(['status' => 200, 'message' => 'Account details not found.']);
        	}else{
        		$account = $stripe->accounts->retrieve('acct_1P4iRNGfa6Hi2GZB', []);
			//echo $request['amount']*100; exit;
        		if(count($account->external_accounts->data)>0){
	        		$account_id =  'acct_1P4iRNGfa6Hi2GZB';//Auth::guard('provider')->user()->stripe_acct_id;
	        		$transfer = $stripe->transfers->create([
						  'amount' => $request['amount'],
						  'currency' => 'usd',
						  'destination' => $account_id,
						  'transfer_group' => 'ORDER_95',
						]);
        			return $transfer;
        		}else{
        			return Helper::getResponse(['status' => 200, 'message' => 'Provider not completed form']);
        		} 
        	}
       }
	}
}