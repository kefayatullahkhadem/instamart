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
            $payment_type = Session::get('payment_type');
            
            switch ($payment_type) {
                case 'cart_payment':
                    $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                    $amount = $combined_order->grand_total;
                    break;
                case 'wallet_payment':
                    $amount = Session::get('payment_data')['amount'];
                    break;
                case 'customer_package_payment':
                    $customer_package = CustomerPackage::findOrFail(Session::get('payment_data')['customer_package_id']);
                    $amount = $customer_package->amount;
                    break;
                case 'seller_package_payment':
                    $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
                    $amount = $seller_package->amount;
                    break;
                default:
                    $amount = 0; // Set a default value for $amount if payment_type is not recognized.
            }
        }

        $user = Auth::user();
        $fullName = $user->name ?: 'yuga';
        $nameParts = explode(' ', $fullName);
        $firstName = count($nameParts) >= 1 ? $nameParts[0] : 'yuga';
        $lastName = count($nameParts) > 1 ? $nameParts[count($nameParts) - 1] : 'yuga';

        $defaultPhoneNumber = '0111225112';
        $userPhoneNumber = $user->phone_number;
        $contact_no = $userPhoneNumber ?? $defaultPhoneNumber;

        $userEmail = $user->email;
        $email = $userEmail ?? 'someone@example.com';

        $payment_type = Session::get('payment_type');
        $combined_order_id = Session::get('combined_order_id');
        $payment_data = Session::get('payment_data');
        
        $description = $payment_data
            ? 'Payment Type: ' . $payment_type . ' - Payment Data: ' . json_encode($payment_data)
            : 'Payment Type: ' . $payment_type . ' - Combined Order ID: ' . $combined_order_id;

        $batch = 'New';
        $currency = Config::get('webxpay.default_currency_code');
        $secret_key = Config::get('webxpay.secret_key');
        $amount = number_format((float)$amount, 2, '.', '');
        $url = Config::get('webxpay.webx_url');

        $plaintext = $combined_order->id . '|' . '2';
        $publickey = base64_decode(Config::get('webxpay.public_key'));

        if (openssl_public_encrypt($plaintext, $encrypt, $publickey)) {
            // Encryption successful, $encrypt now contains the encrypted data
        } else {
            // Encryption failed, check for errors
            $error = openssl_error_string();
            dd("Encryption error: " . $error);
        }

        $payment = base64_encode($encrypt);
        $custom_fields = base64_encode($description);

        $postData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'contact_number' => $contact_no,
            'address_line_one' => 'Colombo',
            'cms' =>  "PHP",
            'process_currency' => "LKR",
            'payment_gateway_id' => '',
            'bankMID' => 'TESTWEBXTOKMSUSD',
            'custom_fields' => $custom_fields,
            'enc_method' => 'JCs3J+6oSz4V0LgE0zi/Bg==',
            'multiple_payment_gateway_ids' => "2|3|4|5|35|43|96",
            'payment' => $payment,
            'secret_key' => $secret_key,
        ];

        // Redirect the customer to the URL
        echo "<form id='redirectForm' action='$url' method='post'>";
        foreach ($postData as $key => $value) {
            echo "<input type='hidden' name='$key' value='$value'>";
        }
        echo "</form>";
        echo "<script>document.getElementById('redirectForm').submit();</script>";
    }

    public function getDone(Request $request)
    {
        dd($request);
        // Rest of the function remains unchanged
    }
}
