<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    // vendor/laravel/ui/auth-backend/AuthenticatesUsers.php
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/memo';

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|max:255|email',
            'password' => 'required|min:8|max:255|regex:/^[a-zA-Z0-9]+$/',
        ],
        [
            'password.regex' => ':attributeは半角英数字で入力してください。'
        ]);
    }

    // オーバライドしている
    protected function authenticated(Request $request, $user)
    {
        $memo = Memo::where('user_id', '=', Auth::id())->orderBy('updated_at', 'desc')->first();

        if($memo) session()->put('select_memo', $memo);
    }

    public function profileChangeDisplay()
    {
        $user = Auth::user();

        return view('user.profile', ['user' => $user]);
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255|regex:/^[a-zA-Z0-9]+$/',
        ]);
        
        $user = User::where('id', $request->user_id)->first();
        $user->name = $request->name;
        $user->save();
        
        return redirect()->route('memo.index');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        if(url()->current() !== "http://localhost:8085/user/profile" && url()->current() !== "http://localhost:8085/user/profile/update"){
            $this->middleware('guest')->except('logout');
        }
    }
}
