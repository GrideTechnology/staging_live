<?php

namespace App\Jobs;

class PushNotificationJob extends Job
{
	protected $topic, $push_message, $title, $data, $user, $settings, $type;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($topic, $push_message, $title, $data, $user, $settings, $type)
	{		
		$this->topic = $topic;
		$this->push_message = $push_message;
		$this->title = $title;
		$this->data = $data;
		$this->user = $user;
		$this->settings = $settings;
		$this->type = $type;		
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{		
		if($this->user->device_type == 'IOS') {
			
			if($this->type == 'user') {
				$pem = app()->basePath('storage/app/public/'.$this->user->company_id.'/apns' ).'/user.pem';
			} elseif($this->type == 'provider')  {
				$pem = app()->basePath('storage/app/public/'.$this->user->company_id.'/apns' ).'/provider.pem';
			} else {
				$pem = app()->basePath('storage/app/public/'.$this->user->company_id.'/apns' ).'/shop.pem';
			}

			if(file_exists($pem)) {
				//03e84aab5b543ba5a304469a85b0cff5673e01be7213ce23c3ba4cfc7ed0437c				
				
				$config = [
					'environment' => $this->settings->site->environment,
					'certificate' => $pem,
					'passPhrase'  => '',//$this->settings->site->ios_push_password,
					'service'     => 'apns'
					
				];
				
			}
			

		}elseif($this->user->device_type == 'ANDROID'){	
			//Android Token:- f9QWI3sihpM:APA91bGL0OpdCGZmR-zhTGQ9s2LfSPOaC03KcFgjJUfnLlwRonuZfXjC_2snShg9FnuKlk3HTAMSTV_qr9eXJaqD1cO3a1Iq8QFgIcNQ-K6oPcEah1zlyqkzL15fSPuWwsaX9Z-Suxtu
			if($this->settings->site->android_push_key != "") {
				$config = [
					'environment' => $this->settings->site->environment,
					'apiKey'      => $this->settings->site->android_push_key, 
					'service'     => 'gcm'
				];   
			}			
		}

		$message = \PushNotification::Message($this->push_message, array(
			'badge' => 1,
			'sound' => 'default',
			'custom' => [ "message" => [ "topic" => $this->topic, "notification" => [ "body" => $this->push_message, "title" => $this->title ], "data" => $this->data ] ]
		));		
		
				
		if(isset($config) && count($config) > 0 ) {		
			
			return \PushNotification::app($config)
				->to($this->user->device_token)
				->send($message);
				
		}
		
	}
}
