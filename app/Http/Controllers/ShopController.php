<?php

namespace App\Http\Controllers;

use App\Mail\EmailManager;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\User;
use App\Models\BusinessSetting;
use Auth;
use Hash;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;


class ShopController extends Controller
{

    public function __construct()
    {
        $this->middleware('user', ['only' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shop = Auth::user()->shop;
        return view('seller.shop', compact('shop'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::check()) {
            if((Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'customer')) {
                flash(translate('Admin or Customer can not be a seller'))->error();
                return back();
            } if(Auth::user()->user_type == 'seller'){
                flash(translate('This user already a seller'))->error();
                return back();
            }

        } else {
            return view('frontend.seller_form');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users|max:255',
            'password'  => 'required|string|min:6|confirmed',
            'shop_name' => 'required|max:255',
            'address'   => 'required',
            'phone'   => 'required',
        ]);

        $user = null;
        if (!Auth::check()) {
            if (User::where('email', $request->email)->first() != null) {
                flash(translate('Email already exists!'))->error();
                return back();
            }
            if ($request->password == $request->password_confirmation) {
                $user = new User;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->user_type = "seller";
                $user->password = Hash::make($request->password);
                $user->save();
            } else {
                flash(translate('Sorry! Password did not match.'))->error();
                return back();
            }
        } else {
            $user = Auth::user();
            if ($user->customer != null) {
                $user->customer->delete();
            }
            $user->user_type = "seller";
            $user->save();
        }

        if (Shop::where('user_id', $user->id)->first() == null) {
            $shop = new Shop;
            $shop->user_id = $user->id;
            $shop->name = $request->shop_name;
            $shop->address = $request->address;
            $shop->slug = preg_replace('/\s+/', '-', str_replace("/"," ", $request->shop_name));

            if ($shop->save()) {
                auth()->login($user, false);
                if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
                    $user->email_verified_at = date('Y-m-d H:m:s');
                    $user->save();
                } else {
//                    $user->notify(new EmailVerificationNotification());
                }
                //  send email
                $mail = 'sellers@instamart.lk';
//                $mail = 'sankaamazon@gmail.com';
                $email = $request->email;
                $shopname = $request->shop_name;
                $phone = $request->phone;
                $Address = $request->address;
                $name = $request->name;

                $this->sendEmailNewSeller($email,$name);
                $this->emailadminsellerCrate($mail,$email,$shopname,$phone,$Address);

                flash(translate('Your Shop has been created successfully!'))->success();

                return redirect()->route('seller.shop.index');
            } else {
                $user->user_type == 'customer';
                $user->save();
            }
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * send admin seller register email
     */
    public function emailadminsellerCrate($mail,$email,$shopname,$phone,$Address){
        $htmlContent = '
            <html>
                <head>
                    <title>Registered New Seller</title>
                </head>
                <body>
                    <h1>Registered New Seller</h1>
                    <table cellspacing="0" style="border: 2px dashed #FB4314; width: 100%;">
                        <tr>
                            <th>Email :</th><td>'.$email.'</td>
                        </tr>
                        <tr style="background-color: #e0e0e0;">
                            <th>Shop Name :</th><td>'.$shopname.'</td>
                        </tr>
                        <tr>
                            <th>Phone :</th><td>'.$phone.'</td>
                        </tr>
                        <tr>
                            <th>Address :</th><td>'.$Address.'</td>
                        </tr>
                    </table>
                </body>
            </html>';
        $array['view'] = 'emails.newsletter';
        $array['subject'] = "Registered New Seller!";
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = $htmlContent;
        try {
            Mail::to($mail)->queue(new EmailManager($array));
        } catch (\Exception $e) {
            dd($e);
        }

        return back();
    }

    /**
     * Send mail to Seller
     */
    public function sendEmailNewSeller($email,$name){
        $encryptedData = Crypt::encrypt($email);
        $link = 'https://instamart.lk/emailverification/'.$encryptedData;
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
      <img align="center" border="0" src="https://i.ibb.co/R3GBcXH/submit-your-documents-and-start-selling.png" alt="" title="" style="outline:none;text-decoration:none;clear:both;display:inline-block!important;border:none;height:auto;float:none;width:100%;max-width:680px" width="680" class="CToWUd" data-bit="iit">
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

<p style="font-size:14px;line-height:140%"><span style="font-family:arial,helvetica,sans-serif;font-size:16px;line-height:22.4px">Your email account has been verified, but your seller account still needs to be verified.</span></p>
<p style="font-size:14px;line-height:140%"><span style="font-family:arial,helvetica,sans-serif;font-size:16px;line-height:22.4px">You are only a few steps away from selling on Sri Lanka'.'s Largest Online Marketplace!</span></p>
<p style="font-family:arial,helvetica,sans-serif; font-size:14px;">
      <a href="'.$link.'" style = "color: #ffffff;
      text-decoration: none;
      background-color: #CB227C;
      padding: 8px 10px;
      border-radius: 4px;">Click here</a> to login to your Seller Dashboard.
    </p>
    <p style="font-family:arial,helvetica,sans-serif; font-size:14px;">
        Once you login to your seller dashboard, submit the basic information requirements and earn the verified badge.
        Once you earn the verified badge, you can start selling on Instamart.lk.
    </p>
    <img src="https://i.ibb.co/rQdR5P0/Sellerveryfication.png">

    <p style="font-family:arial,helvetica,sans-serif; font-size:14px;">For more clarification, feel free to contact the seller support if you have any concerns!</p>
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
    <div style="font-size:0pt;line-height:0pt;text-align:center;padding-bottom:0px"><img src="https://i.ibb.co/WKbZ7hJ/logo.png" height="150" style="max-width:240px" border="0" alt="" class="CToWUd" data-bit="iit"></div>
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
        $array['view'] = 'emails.newsletter';
        $array['subject'] = "Congratulations! Start Selling Now!";
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = $htmlContent;
        try {
            Mail::to($email)->queue(new EmailManager($array));
        } catch (\Exception $e) {
            dd($e);
        }

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
