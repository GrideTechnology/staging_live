<?php

namespace App\Http\Controllers\V1\Transport\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SendPushNotification;
use App\Models\Transport\RideCars;
use App\Models\Transport\RideRentalCharges;
use App\Models\Transport\RideCarsImages;
use App\Models\Transport\RideCarsType;
use App\Models\Transport\RideRental;
use App\Models\Common\Setting;
use App\Models\Common\User;
use App\Models\Common\PaymentLog;
use App\Helpers\Helper;
use App\Services\V1\Order\Order;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Traits\Actions;
use Auth;
use DB;
use Stripe;
use Illuminate\Support\Facades\Log;

class RentalController extends Controller
{
	use Actions;

	public function dashboard(){
		$user = Auth::guard('user')->user();
		$booking = RideRental:: select('ride_cars.year','ride_cars.make','ride_cars.model','ride_cars.vin','ride_cars.protection','ride_cars.daily_charges','ride_cars_images.right', 'ride_rentals.id', 'ride_rentals.booking_start_date', 'ride_rentals.booking_end_date', 'ride_rentals.status')->leftjoin('ride_cars', 'ride_cars.id', '=', 'ride_rentals.car_id' )->leftjoin('ride_cars_images', 'ride_cars_images.car_id', '=', 'ride_cars.id')->where('ride_rentals.user_id', '=', $this->user->id);
		$booking->orderBy('ride_rentals.id', 'desc')->limit(5);

		$weeklybookings = RideRental::where('user_id', $this->user->id)->whereBetween('booking_start_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

		$data['carlist'] = $booking->get();
		$data['weekly'] = $weeklybookings->count();
		return Helper::getResponse(['data' => $data]);
	}
	public function bookcar(Request $request, $id){
		$cars = RideCars::find($id);

		$user = Auth::guard('user')->user();

		$days = $request->days;

		$method = $request->method;

		$enddate = date('Y-m-d', strtotime($request->drop_date));//date('Y-m-d', strtotime($request->fromdate. ' + '.$days.' days'));
		$startdate= date('Y-m-d', strtotime($request->pickup_date));

		$lastbooking = RideRental:: latest('id')->first();

		$today = date('Y-m-d');

		if($today>$startdate){
			return Helper::getResponse(['status' => 200, 'message' => trans('Please select current date or future date.'), 'error' => trans('Please select current date or future date.')]);
		}

		$isbooked = RideRental:: where('car_id', $id)->where('booking_start_date', '>=', $startdate)->where('booking_end_date', '<=', $enddate)->where('status', 'IN', array('4', '7'))->count();

		require base_path().'/vendor/autoload.php';

		
        $stripe = new \Stripe\StripeClient();

		if($isbooked>0){
			return Helper::getResponse(['status' => 200, 'message' => trans('Already booked slot for this date'), 'error' => trans('Already booked slot for this date')]);
		}else{
			
			$admincharges = RideRentalCharges::find(1);

			if($request->pricing=="WEEKLY"){
				$w = (float)$days/7;
				$week = ceil($w);
				$rent = $cars->weekly_charges*(int)$week;
			}else{
				$rent = $cars->daily_charges* $days;
			}

			$insurance= $admincharges->insurance_fee*$days;
			$booking_amt = $admincharges->booking_fee*$days;

			$subtotal = $rent+$insurance+$booking_amt;

			$tax= ($subtotal/100)*$admincharges->sales_tax;

			$total = $subtotal+$tax;
			
			if($request->method==''){
				$method='card';
			}
			

			if($method=='card'){
				
				//$isCardExist = RideRental:: where('card_number', '=', $request->card_number)->where('user_id', '=', $this->user->id)->orderBy('id', 'DESC')->limit(1)->get();


				//if(count($isCardExist)==0){
					$paymentMethod = $stripe->paymentMethods->create([
								    'type' => 'card',
								    'card' => [
								      'number' => $request->card_number,
								      'exp_month' => $request->card_exp_month,
								      'exp_year' => $request->card_exp_year,
								      'cvc' => $request->card_cvv,
								    ],
								  ]);
					$methodid=$paymentMethod->id;
				
				// }else{
				// 	$methodid = $isCardExist[0]->stripe_card_id;
				// }


				if(empty($methodid)){
					return Helper::getResponse(['status' => 200, 'message' => 'Invalid card details' ]);
				}


				if($this->user->stripe_cust_id!='')
				{
					$custID=$this->user->stripe_cust_id;
				}else{
					$customer=$stripe->customers->create([
							'email' => $this->user->email,
					    	'description' => $this->user->first_name.' '.$this->user->last_name,
					    ]);
					$custID=$customer->id;
					//update user's customer id

					$usersinfo = User::findorFail($this->user->id);
					$usersinfo->stripe_cust_id = $custID;
					$usersinfo->save();

				}

				//cus_Ls3KRxlRJXEh9M

				if(!empty($custID)){
					$custMethod=$stripe->paymentMethods->attach(
					    $methodid,
					    ['customer' => $custID]
					);

				
					if(!empty($custMethod->id)){

					//Payment Function
										    	
						$data=array('user_id'=>$this->user->id, 'company_id'=>$this->user->company_id, 'payment_mode'=>'CARD', 'payment_method'=>$custMethod->id);


				    	
						//$payment = Order::bookingpayment($total, $data); 
				    	
				    	// $paymentid = $payment['payment_id'];

				    	// $date=date('Y-m-d');
				    	// $time=date('H:i:s');
				    	
				    //Payment Function
				    //if($payment['responseMessage']=='success')
						$booking = new RideRental;
						$booking->user_id = $this->user->id;
						$booking->owner_id = $cars->owner_id;
						$booking->booking_id = $lastbooking->booking_id+1;
						$booking->car_id = $id;
						$booking->pricing = $request->pricing;
						$booking->fname = $request->fname;
						$booking->lname = $request->lname;
						$booking->zipcode = $request->zipcode;
						$booking->stripe_card_id = $custMethod->id;
						$booking->card_number = $request->card_number;
						$booking->card_cvv = $request->card_cvv;
						$booking->card_exp_month = $request->card_exp_month;
						$booking->card_exp_year = $request->card_exp_year;
						$booking->booking_start_date = date('Y-m-d', strtotime($request->pickup_date));
						$booking->booking_end_date = $enddate;
						$booking->days = $days;	
						$booking->rent_amount = $rent;
						$booking->insurance_amount = $insurance;
						$booking->booking_amount = $booking_amt;
						$booking->tax_amount = $tax;
						$booking->total_amount = $total;	
		
	
						if($booking->save()){
							return Helper::getResponse(['status' => 200, 'message' => 'Booking successfull.' ]);
						}else{
							return Helper::getResponse(['status' => 200, 'message' =>'card_number:'.$request->card_number.' | Card CVV: '. $request->card_cvv.' | Card exp: '.$request->card_exp_month.$request->card_exp_year]);
							return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
						}

					}
				}else{
					return Helper::getResponse(['status' => 200, 'message' => 'Customer not found in stripe' ]);
				}

			}else{
			 	$isBankExist = RideRental:: where('routing_number', '=', $request->routing_number)->where('account_number', '=', $request->account_number)->where('user_id', '=', $this->user->id)->orderBy('id', 'DESC')->limit(1)->get();

			 	$custID = $this->user->stripe_cust_id;

			 	if($custID=='')
				{
					$customer=$stripe->customers->create([
							'email' => $this->user->email,
					    	'description' => $this->user->first_name.' '.$this->user->last_name,
					    ]);
					$custID=$customer->id;
					//update user's customer id

					$usersinfo = User::findorFail($this->user->id);
					$usersinfo->stripe_cust_id = $custID;
					$usersinfo->save();
				}

				if(count($isBankExist)==0){
					    $bank = $stripe->customers->createSource(
						                $custID,
						                ['source' => [
					                        'object' => 'bank_account',
					                        'country' => 'US',
					                        'currency' => 'usd',
					                        'account_number' => $request->account_number,
					                        'routing_number'=> $request->routing_no,
					                        'account_holder_type'=> 'individual',
					                        'account_holder_name' => Auth::guard('user')->user()->first_name.' '.Auth::guard('user')->user()->last_name
								              ]
								          ]
					              );
		            
	                	$booking = new RideRental;
						$booking->user_id = $this->user->id;
						$booking->owner_id = $cars->owner_id;
						$booking->booking_id = $lastbooking->booking_id+1;
						$booking->car_id = $id;
						$booking->pricing = $request->pricing;
						$booking->fname = $request->fname;
						$booking->lname = $request->lname;
						$booking->licence_number = $request->licence_number;
						$booking->licence_state = $request->licence_state;
						$booking->zipcode = $request->zipcode;
						$booking->stripe_bank_id = $bank->id;
						$booking->account_number = $request->account_number;
						$booking->routing_number = $request->routing_no;
						$booking->booking_start_date = date('Y-m-d', strtotime($request->pickup_date));
						$booking->booking_end_date = $enddate;
						$booking->days = $days;	
						$booking->rent_amount = $rent;
						$booking->insurance_amount = $insurance;
						$booking->booking_amount = $booking_amt;
						$booking->tax_amount = $tax;
						$booking->total_amount = $total;	
						// $booking->txn_id = $paymentid;
						// $booking->is_paid = 1;
				    	// $booking->paid_on_date = $date;
				    	// $booking->paid_on_time = $time;

						if($booking->save()){
							return Helper::getResponse(['status' => 200, 'message' => 'Booking successfull.' ]);
						}else{
							return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
						}

			    }else{
					$booking = new RideRental;
					$booking->user_id = $this->user->id;
					$booking->owner_id = $cars->owner_id;
					$booking->booking_id = $lastbooking->booking_id+1;
					$booking->car_id = $id;
					$booking->pricing = $request->pricing;
					$booking->fname = $request->fname;
					$booking->lname = $request->lname;
					$booking->licence_number = $request->licence_number;
					$booking->licence_state = $request->licence_state;
					$booking->zipcode = $request->zipcode;
					$booking->stripe_bank_id = $isBankExist[0]->stripe_bank_id;;
					$booking->account_number = $request->account_number;
					$booking->routing_number = $request->routing_no;
					$booking->booking_start_date = date('Y-m-d', strtotime($request->pickup_date));
					$booking->booking_end_date = $enddate;
					$booking->days = $days;	
					$booking->rent_amount = $rent;
					$booking->insurance_amount = $insurance;
					$booking->booking_amount = $booking_amt;
					$booking->tax_amount = $tax;
					$booking->total_amount = $total;	
					// $booking->txn_id = $paymentid;
					// $booking->is_paid = 1;
			    	// $booking->paid_on_date = $date;
			    	// $booking->paid_on_time = $time;

					if($booking->save()){
						return Helper::getResponse(['status' => 200, 'message' => 'Booking successfull.' ]);
					}else{
						return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
					}
				} 

			}

		}
	}

	public function bookedcar(Request $request){
		$user = Auth::guard('user')->user();
		$search= $request->keyword;
		$booking = RideRental:: select('ride_cars.year', 'ride_cars.pickup_address','ride_cars.make','ride_cars.model','ride_cars.vin','ride_cars.protection','ride_cars.daily_charges','ride_cars_images.right', 'ride_rentals.id', 'ride_rentals.status', 'ride_rentals.total_amount', 'ride_rentals.booking_start_date', 'ride_rentals.booking_end_date')->leftjoin('ride_cars', 'ride_cars.id', '=', 'ride_rentals.car_id' )->leftjoin('ride_cars_images', 'ride_cars_images.car_id', '=', 'ride_cars.id')->where('ride_rentals.user_id', '=', $this->user->id);

							if($search!=''){
							    $booking->where(function($query) use ($search){
							        $query->orWhere('ride_cars.model', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.vin', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.year', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.make', 'LIKE', '%'.$search.'%');
							    });
							}
		$booking->orderBy('ride_rentals.id', 'desc');

		$data['carlist'] = $booking->get();

		return Helper::getResponse(['data' => $data]);
		
	}

	public function userSpendingsHinstory(Request $request){
		$user = Auth::guard('user')->user();
		$search= $request->keyword;
		$booking = RideRental:: select('ride_cars.year', 'ride_cars.pickup_address','ride_cars.make','ride_cars.model','ride_cars.vin','ride_cars.protection','ride_cars.daily_charges','ride_cars_images.right', 'ride_rentals.id', 'ride_rentals.status', 'ride_rentals.total_amount')->leftjoin('ride_cars', 'ride_cars.id', '=', 'ride_rentals.car_id' )->leftjoin('ride_cars_images', 'ride_cars_images.car_id', '=', 'ride_cars.id')->where('ride_rentals.user_id', '=', $this->user->id);

							if($search!=''){
							    $booking->where(function($query) use ($search){
							        $query->orWhere('ride_cars.model', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.vin', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.year', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.make', 'LIKE', '%'.$search.'%');
							    });
							}
		$booking->orderBy('ride_rentals.id', 'desc');

		$data['carlist'] = $booking->get();

		return Helper::getResponse(['data' => $data]);
		
	}

	public function carstatus($id){
		$date = date('Y-m-d');
		$data = RideRental::select('booking_start_date', 'booking_end_date')->where('car_id', '=', $id)->where('status', 'IN', array('4', '7', ))->get();		

		return Helper::getResponse(['data' => $data]);

	}
	public function carAvailability(Request $request, $id){
		$enddate = date('Y-m-d', strtotime($request->end));
		$startdate= date('Y-m-d', strtotime($request->start));
		$today = date('Y-m-d');
		$isbooked = RideRental:: where('car_id', $id)->where('booking_start_date', '>=', $startdate)->where('booking_end_date', '<=', $enddate)->where('booking_start_date', '>', $today)->where('status', 'IN', array('4', '7'))->count();		

		return Helper::getResponse(['data' => $isbooked]);
	}

	public function rentalDetails($id){
		$booking = RideRental:: select('ride_cars.*','ride_cars_images.right', 'ride_rentals.*', 'owners.first_name', 'owners.last_name','owners.phone','owners.email', 'owners.picture', 'ride_common.states.state_name')->leftjoin('ride_cars', 'ride_cars.id', '=', 'ride_rentals.car_id' )->leftjoin('ride_cars_images', 'ride_cars_images.car_id', '=', 'ride_cars.id')->leftjoin('ride_common.owners', 'ride_common.owners.id', '=', 'ride_cars.owner_id')->leftjoin('ride_common.states', 'ride_common.states.id', '=', 'ride_rentals.licence_state')->where('ride_rentals.id', '=', $id);

		$booking->orderBy('ride_rentals.id', 'desc');

		$data = $booking->get();

		return Helper::getResponse(['data' => $data]);
	}
}
?>