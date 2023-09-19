<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Customer;
use App\Models\Cart;
use App\Models\BusinessSetting;
use App\OtpConfiguration;
use App\Http\Controllers\Controller;
use App\Http\Controllers\OTPVerificationController;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Cookie;
use Session;
use Nexmo;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Mail\EmailManager;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        }
        else {
            if (addon_is_activated('otp_system')){
                $user = User::create([
                    'name' => $data['name'],
                    'phone' => '+'.$data['country_code'].$data['phone'],
                    'password' => Hash::make($data['password']),
                    'verification_code' => rand(100000, 999999)
                ]);

                $otpController = new OTPVerificationController;
                $otpController->send_code($user);
            }
        }

        if(session('temp_user_id') != null){
            Cart::where('temp_user_id', session('temp_user_id'))
                ->update([
                    'user_id' => $user->id,
                    'temp_user_id' => null
                ]);

            Session::forget('temp_user_id');
        }

        if(Cookie::has('referral_code')){
            $referral_code = Cookie::get('referral_code');
            $referred_by_user = User::where('referral_code', $referral_code)->first();
            if($referred_by_user != null){
                $user->referred_by = $referred_by_user->id;
                $user->save();
            }
        }

        return $user;
    }

    public function register(Request $request)
    {
        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            if(User::where('email', $request->email)->first() != null){
                flash(translate('Email or Phone already exists.'));
                return back();
            }
        }
        elseif (User::where('phone', '+'.$request->country_code.$request->phone)->first() != null) {
            flash(translate('Phone already exists.'));
            return back();
        }

        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        $this->guard()->login($user);

        if($user->email != null){
            if(BusinessSetting::where('type', 'email_verification')->first()->value != 1){
                $user->email_verified_at = date('Y-m-d H:m:s');
                $user->save();
                flash(translate('Registration successful.'))->success();
            }
            else {
                try {
                    $mail = $request->email;
//                    $user->sendEmailVerificationNotification();
                    $this->sendEmailNewSeller($mail);

                    flash(translate('Registration successful. Please verify your email.'))->success();
                } catch (\Throwable $th) {
                    $user->delete();
                    flash(translate('Registration failed. Please try again later.'))->error();
                }
            }
        }

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    public function sendEmailNewSeller($email)
    {
        $encryptedData = Crypt::encrypt($email);
        $link = 'https://instamart.lk/emailverification/' . $encryptedData;

        $htmlContent = '
         <!DOCTYPE html>
        <html>
<head>
  <meta charset="UTF-8">
  <title>Instamart.lk Seller Verification</title>
  <style>
    body {
      background-color: #f6f6f6;
      color: #333333;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      background-color: #ffffff;
      border-radius: 4px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    h2 {
      color: #CB227C;
      font-size: 24px;
      margin: 0 0 20px;
    }
    p {
      margin: 0 0 20px;
      font-size: 18px;
    }

    img.logo {
      display: block;
      margin: 20px auto;
      max-width: 400px;
      height: auto;
    }
    img.logox {
      display: block;
      margin: 20px auto;
      max-width: 100px;
      height: auto;
    }
    .contact-info {
      margin-top: 20px;
    }
    .contact-info p {
      margin: 0;
    }
  </style>
</head>
<body>
  <div style="margin:0;padding:0;background-color:#ffffff;color:#000000">
  <table id="m_-1881019909769329687m_-7720350875776880515m_8687697329421142872u_body" style="border-collapse:collapse;table-layout:fixed;border-spacing:0;vertical-align:top;min-width:320px;Margin:0 auto;background-color:#ffffff;width:100%" cellpadding="0" cellspacing="0">
  <tbody>
  <tr style="vertical-align:top">
    <td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top">

<div style="padding:0px;background-color:transparent">
  <div style="Margin:0 auto;min-width:320px;max-width:700px;word-wrap:break-word;word-break:break-word;background-color:transparent">
    <div style="border-collapse:collapse;display:table;width:100%;height:100%;background-color:transparent">

<div style="max-width:320px;min-width:700px;display:table-cell;vertical-align:top">
  <div style="height:100%;width:100%!important">
  <div style="box-sizing:border-box;height:100%;padding:0px;border-top:0px solid transparent;border-left:0px solid transparent;border-right:0px solid transparent;border-bottom:0px solid transparent">

<table style="font-family:georgia,palatino" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody>
    <tr>
      <td style="word-break:break-word;padding:10px;font-family:georgia,palatino" align="left">

<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tbody><tr>
    <td style="padding-right:0px;padding-left:0px" align="center">
      <img align="center" border="0" src="https://i.ibb.co/kJhXJTf/Whats-App-Image-2023-07-13-at-22-07-23.jpg" alt="" title="" style="outline:none;text-decoration:none;clear:both;display:inline-block!important;border:none;height:auto;float:none;width:100%;max-width:680px" width="680" class="CToWUd" data-bit="iit">
    </td>
  </tr>
</tbody></table>

      </td>
    </tr>
  </tbody>
</table>

<table style="font-family:georgia,palatino" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody>
    <tr>
      <td style="word-break:break-word;padding:10px;font-family:georgia,palatino" align="left">

  <div style="line-height:140%;text-align:left;word-wrap:break-word">

<p style="font-size:14px;line-height:140%"><span style="font-family:arial,helvetica,sans-serif;font-size:16px;line-height:22.4px">Your email account has been verified!</span></p>

<p style="font-family:arial,helvetica,sans-serif; font-size:14px;">
      <a href="'.$link.'" style = "color: #ffffff;
      text-decoration: none;
      background-color: #CB227C;
      padding: 8px 10px;
      border-radius: 4px;">Click here</a> to login to your instamart Dashboard..
    </p>

  </div>

      </td>
    </tr>
  </tbody>
</table>

  </div>
  </div>
</div>


    </div>
  </div>
</div>



<div style="padding:0px;background-color:transparent">
  <div style="Margin:0 auto;min-width:320px;max-width:700px;word-wrap:break-word;word-break:break-word;background-color:transparent">
    <div style="border-collapse:collapse;display:table;width:100%;height:100%;background-color:transparent">



<div style="max-width:320px;min-width:350px;display:table-cell;vertical-align:top">
  <div style="height:100%;width:100%!important;border-radius:0px">
  <div style="box-sizing:border-box;height:100%;padding:0px;border-top:0px solid transparent;border-left:0px solid transparent;border-right:0px solid transparent;border-bottom:0px solid transparent;border-radius:0px">

<table style="font-family:georgia,palatino" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody>
    <tr>
      <td style="word-break:break-word;padding:10px;font-family:georgia,palatino" align="left">




      </td>
    </tr>
  </tbody>
</table>

  </div>
  </div>
</div>


<div style="max-width:320px;min-width:350px;display:table-cell;vertical-align:top">
  <div style="height:100%;width:100%!important;border-radius:0px">
  <div style="box-sizing:border-box;height:100%;padding:0px;border-top:0px solid transparent;border-left:0px solid transparent;border-right:0px solid transparent;border-bottom:0px solid transparent;border-radius:0px">

<table style="font-family:georgia,palatino" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody>
    <tr>
      <td style="word-break:break-word;padding:10px;font-family:georgia,palatino" align="left">




      </td>
    </tr>
  </tbody>
</table>

  </div>
  </div>
</div>


    </div>
  </div>
</div>



<div style="padding:0px;background-color:transparent">
  <div style="Margin:0 auto;min-width:320px;max-width:700px;word-wrap:break-word;word-break:break-word;background-color:transparent">
    <div style="border-collapse:collapse;display:table;width:100%;height:100%;background-color:transparent">



<div style="max-width:320px;min-width:233.33px;display:table-cell;vertical-align:top">
  <div style="height:100%;width:100%!important;border-radius:0px">
  <div style="box-sizing:border-box;height:100%;padding:0px;border-top:0px solid transparent;border-left:0px solid transparent;border-right:0px solid transparent;border-bottom:0px solid transparent;border-radius:0px">

<table style="font-family:georgia,palatino" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody>
    <tr>
      <td style="word-break:break-word;padding:10px;font-family:georgia,palatino" align="left">

  <div>
    <div style="font-size:0pt;line-height:0pt;text-align:center;padding-bottom:0px"><img src="https://i.ibb.co/wNqPzt5/BA3-B2-EDC-711-F-439-D-8632-F6-DF06-A75-CC3.jpg" height="150" style="max-width:240px" border="0" alt="" class="CToWUd" data-bit="iit"></div>
  </div>

      </td>
    </tr>
  </tbody>
</table>

  </div>
  </div>
</div>


<div style="max-width:320px;min-width:233.33px;display:table-cell;vertical-align:top">
  <div style="height:100%;width:100%!important;border-radius:0px">
  <div style="box-sizing:border-box;height:100%;padding:0px;border-top:0px solid transparent;border-left:0px solid transparent;border-right:0px solid transparent;border-bottom:0px solid transparent;border-radius:0px">

<table style="font-family:georgia,palatino" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody>
    <tr>
      <td style="word-break:break-word;padding:10px;font-family:georgia,palatino" align="left">

  <div>
    <div style="font-family:'.'Lato'.',Arial,sans-serif;font-size:18px;line-height:28px;font-weight:bold;color:#06617a;text-align:center;padding-bottom:1px"><div>Need more help?</div></div>
<div style="color:#b7e5ff;font-family:'.'Lato'.',Arial,sans-serif;font-size:13px;line-height:24px;text-align:center;padding-bottom:5px"><div><span style="font-size:14px"><span style="color:#06617a">WhatsApp:&nbsp;<span style="text-align:center;background-color:rgb(255,255,255)">+94 765923183</span></span></span></div></div>
<div style="color:#b7e5ff;font-family:'.'Lato'.',Arial,sans-serif;font-size:13px;line-height:24px;text-align:center;padding-bottom:5px"><div><span style="font-size:14px"><span style="color:#06617a">WhatsApp:&nbsp;<span style="text-align:center;background-color:rgb(255,255,255)">+94 767290620</span></span></span></div></div>
<div style="font-family:Arial,sans-serif;font-size:14px;line-height:18px;padding:12px 20px;text-align:center;text-transform:uppercase;font-weight:bold;border-radius:22px;background:#CB227C;color:#b7e5ff"><div><a href="mailto:admin@instamart.lk" style="color:#ffffff;text-decoration:none" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://bit.ly/3re0erh&amp;source=gmail&amp;ust=1684326564396000&amp;usg=AOvVaw3QfldK21QhdVQdPhR_ZJYx"><span style="color:#ffffff;text-decoration:none; ">Contact Us</span></a></div></div>
  </div>
      </td>
    </tr>
  </tbody>
</table>

  </div>
  </div>
</div>


<div style="max-width:320px;min-width:233.33px;display:table-cell;vertical-align:top">
  <div style="height:100%;width:100%!important;border-radius:0px">
  <div style="box-sizing:border-box;height:100%;padding:0px;border-top:0px solid transparent;border-left:0px solid transparent;border-right:0px solid transparent;border-bottom:0px solid transparent;border-radius:0px">

<table style="font-family:georgia,palatino" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody>
    <tr>

  <div><br>
<div style="color:#777777;font-family:'.'Lato'.',Arial,sans-serif;font-size:14px;line-height:24px;text-align:center;padding-bottom:8px"><div>Get the Seller Center App</div></div>
<div style="font-size:0pt;line-height:0pt;text-align:center;padding-bottom:8px"><a href="#" style="text-decoration:none" target="_blank" data-saferedirecturl="#"><img src="https://ci5.googleusercontent.com/proxy/Iis74QYZ8OP1LdJUOjU5CDF871F4YmruONx8N0eoqT6Ujce_spMOYMRGqJFOs41b2QWUwKAJSxnMIoiqYVG3hVnxxB0sgQlbKguUN6-mUJU9MHzS=s0-d-e1-ft#https://img.alicdn.com/tfs/TB1XZxBgEz1gK0jSZLeXXb9kVXa-279-96.jpg" width="105" height="35" style="max-width:105px" border="0" alt="" class="CToWUd" data-bit="iit"></a></div>
<div style="font-size:0pt;line-height:0pt;text-align:center;padding-bottom:8px"><a href="#" style="text-decoration:none" target="_blank" data-saferedirecturl="#"><img src="https://ci6.googleusercontent.com/proxy/D_8FtO-9BwqWiRLsN5PfK3MBbp7y6Hcs58qnsyKyHdNmmLSptVJXFq_86nILOdH9FqTTefyY5Zg9wcX6X5jRn4EhInpZ8jXjmx6oh6GuXKsrmPsk=s0-d-e1-ft#https://img.alicdn.com/tfs/TB1bP8FgEY1gK0jSZFCXXcwqXXa-283-97.jpg" width="105" height="35" style="max-width:105px" border="0" alt="" class="CToWUd" data-bit="iit"></a></div>

  </div>

      </td>
    </tr>
  </tbody>
</table>

  </div>
  </div>
</div>


    </div>
  </div>
</div>



    </td>
  </tr>
  </tbody>
  </table>





</div></div></div>
</body>
</html>
    ';

        $data = [
            'view' => 'emails.newsletter',
            'subject' => "Registered New User!",
            'from' => env('MAIL_FROM_ADDRESS'),
            'content' => $htmlContent,
        ];

        try {
            Mail::to($email)->queue(new EmailManager($data));
        } catch (\Exception $e) {
            dd($e);
        }

        return back();
    }

    protected function registered(Request $request, $user)
    {
        if ($user->email == null) {
            return redirect()->route('verification');
        }elseif(session('link') != null){
            return redirect(session('link'));
        }else {
            return redirect()->route('home');
        }
    }
}
