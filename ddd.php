<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Session;

class WebxpayController extends Controller
{
 
    public function pay(Request $request)
    {

        if (Session::has('payment_type')) {
            if (Session::get('payment_type') == 'cart_payment') {
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                $amount = $combined_order->grand_total;
            } elseif (Session::get('payment_type') == 'wallet_payment') {
                $amount = Session::get('payment_data')['amount'];
            } elseif (Session::get('payment_type') == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail(Session::get('payment_data')['customer_package_id']);
                $amount = $customer_package->amount;
            } elseif (Session::get('payment_type') == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
                $amount = $seller_package->amount;
            }
        }

        // Prepare payload for Webxpay API
        $payload = [
            'amount' => $amount,
            'order_id' => $combined_order,
            // Add other required parameters
        ];

        $fullName = Auth::user()->name;

        if ($fullName) {
            $nameParts = explode(' ', $fullName);
            
            // Check if the array has at least one element (first name)
            if (count($nameParts) >= 1) {
                $firstName = $nameParts[0];
            } else {
                $firstName = 'yuga';
            }
            
            // Check if the array has more than one element (last name)
            if (count($nameParts) > 1) {
                $lastName = $nameParts[count($nameParts) - 1];
            } else {
                $lastName = 'yuga';
            }
        } else {
            $firstName = 'yuga';
            $lastName = 'yuga';
        }
        
        // Now you have $firstName and $lastName with the desired values


        $defaultPhoneNumber = '0111225112';
        $userPhoneNumber = Auth::user()->phone_number;
        
        $contact_no = $userPhoneNumber ?? $defaultPhoneNumber;


        $userEmail = Auth::user()->email;
        $email = $userEmail ?? 'someone@example.com';


        
        $batch = 'New';

        $payment_type = Session::get('payment_type');
        $currency = Config::get('default_currency_code');
        $secret_key=Config::get('webxpay.secret_key');
        $amount = number_format((float)$amount, 2, '.', '');
        $url =Config::get('webxpay.webx_url');

        // unique_order_id|total_amount
$plaintext = $combined_order.'|'.$amount;
$publickey = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDvaWJ96fqWnKkym6pVSHEJxoR7KBBk3CnKivnN/KATb1HipiO9thXNXHOcpkXgeJEYVz+tBUkM5zfKWwB6ChMUbKsWiYEjWOHqdUgdWApcAYfz9smfYrRgYfmvRJWbsXGKahJ5hnp5CvkEgABc0XI8jDuybugaV/IhSQFElIvcIQIDAQAB
-----END PUBLIC KEY-----";
//load public key for encrypting
openssl_public_encrypt($plaintext, $encrypt, $publickey);

//encode for data passing
$payment = base64_encode($encrypt);
//checkout URL
//cus_1|cus_2|cus_3|cus_4
$custom_fields = base64_encode('cus_1|cus_2|cus_3|cus_4');


        // Data to be sent in the POST request
        $postData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'contact_number' => $contact_no,
            'address_line_one' => 'Colombo',
            'address_line_two' => 'Colombo',
            'customer_city' => 'Colombo',
            'customer_state' => 'Colombo',
            'postal_code' => '000012',
            'process_currency' => $currency,
            'payment_gateway_id' => '',
            'bankMID' => 'TESTWEBXTOKMSUSD',
            'custom_fields' => $custom_fields,
            'enc_method' => 'JCs3J+6oSz4V0LgE0zi/Bg==',
            'multiple_payment_gateway_ids' => '2|3|4|5|35|96',
            'secret_key' => $secret_key,
            'payment' => $payment,
            'url' => $url,
        ];

        return view('payment', ['postdata' => $postData]);




 

    }


    /**
     * Handle the callback from Webxpay after payment completion.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function webxpayCallback(Request $request)
    {
        $publickey = base64_decode(Config::get('key_values.public_key'));
        $payment = base64_decode($request ["payment"]);
        $signature = base64_decode($request ["signature"]);
        $custom_fields = base64_decode($request ["custom_fields"]);
//load public key for signature matching


        openssl_public_decrypt($signature, $value, $publickey);

        $responseVariables = explode('|', $payment);

        $combined_order = CombinedOrder::findOrFail($responseVariables[0]);

        foreach ($combined_order->orders as $order) {
            $order->payment_status = 'paid';
            $order->payment_details = json_encode($payment);
            $order->save();
        }

        flash(translate('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
        return redirect()->route('order_confirmed');
    }
}
