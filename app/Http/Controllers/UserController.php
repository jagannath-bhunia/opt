<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use GuzzleHttp\Client;
use Validator;

class UserController extends Controller
{
    public $successStatus = 200;

    public function login(Request $request){
        Log::info($request);
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            return view('home');
        }
        else{
            return Redirect::back ();
        }
    }


    public function smsRequest()
{

    $phone=7001894852;
    $msg='juiy';
    $client = new \GuzzleHttp\Client();
    $url = "http://weberleads.in/http-tokenkeyapi.php?authentic-key=367616f6e6b696164656c3435391582279225&senderid=GAOND&route=2&number=91".$phone."&message=".$msg;
    $request = $client->get($url);
    $response = $request->getBody();
}




    public function loginWithOtp(Request $request){
        Log::info($request);
        $user  = User::where([['mobile','=',request('mobile')],['otp','=',request('otp')]])->first();
        if( $user){
            Auth::login($user, true);
            User::where('mobile','=',$request->mobile)->update(['otp' => null]);
            return view('home');
        }
        else{
            return Redirect::back ();
        }
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => ['required','unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        User::create($input);

        return redirect('login');
    }

    public function sendOtp(Request $request){

        $otp = rand(1000,9999);
        Log::info("otp = ".$otp);
        $user = User::where('mobile','=',$request->mobile)->update(['otp' => $otp]);
        self::smsRequest($request->mobile,"Use This otp for Login".$otp);
       
        return response()->json([$user],200);
    }
}
