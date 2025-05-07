<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class PaymentController extends Controller
{
    public function createPayment(Request $request)
    {
        //return "fsdfs";


        $client = new Client;
        
        $response = $client->post('https://api.sandbox.paypal.com/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => 100.00, // Replace with your desired amount
                    ],
                ],
            ],
        ]);


        $response = $request->getBody();

        dd($response);
        

        // Redirect to PayPal for payment approval
        return redirect($data['links'][1]['href']);
    }

    public function handlePaymentResponse(Request $request)
    {
        return "fsdafs";
        // Handle the response from PayPal after payment
        // Extract transaction details from $request and process accordingly
    }
}

