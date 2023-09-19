<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Foundation\Auth\VerifiesEmails;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\OTPVerificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ShopController;

class VerificationController extends Controller
{
    protected $ShopController;
    protected $RegisterController;

    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ShopController $ShopController , RegisterController $RegisterController)
    {
        //$this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
        $this->ShopController = $ShopController;
        $this->RegisterController = $RegisterController;
    }

    /**
     * Show the email verification notice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if ($request->user()->email != null) {
            return $request->user()->hasVerifiedEmail()
                ? redirect($this->redirectPath())
                : view('auth.verify');
        }
        else {
            $otpController = new OTPVerificationController;
            $otpController->send_code($request->user());
            return redirect()->route('verification');
        }
    }


    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath());
        }
        $email = Auth::user()->email;
        $name = Auth::user()->name;
        $user_type = Auth::User()->user_type;
        if($user_type == 'seller'){
            $this->ShopController->sendEmailNewSeller($email,$name);
        }else{
            $this->RegisterController->sendEmailNewSeller($email);
        }

        return back()->with('resent', true);
    }

    public function verification_confirmation($code){
        $user = User::where('verification_code', $code)->first();
        if($user != null){
            $user->email_verified_at = Carbon::now();
            $user->save();
            auth()->login($user, true);
            flash(translate('Your email has been verified successfully'))->success();
        }
        else {
            flash(translate('Sorry, we could not verifiy you. Please try again'))->error();
        }

        if($user->user_type == 'seller') {
            return redirect()->route('seller.dashboard');
        }

        return redirect()->route('dashboard');
    }

    /**
     * @param $email
     * @return \Illuminate\Http\RedirectResponse
     * my fix seller email verification
     */
    public function selleremailverification($email){

        $decryptedData = Crypt::decrypt($email);
        $now = date('Y-m-d H:m:s');
        DB::table('users')
            ->where('email', $decryptedData)
            ->update(['email_verified_at' => $now ]);

        $user = User::where('email', $decryptedData)->first();
        if($user->user_type == 'seller'){
            if(Auth::check()) {
                return redirect()->route('seller.dashboard');
            }else{
                return redirect()->route('seller.login');
            }
        }else{
            return redirect()->route('user.login');
        }


    }
}
