<?php

namespace App\Http\Controllers\Auth;

use App\Utils\JWTHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function index(Request $request) {
        // check username for login is name or email
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = Auth::guard('web')->user();

        $payload = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'name' => $user->name
        ];

        $jwt = JWTHelper::generate($payload);

        return ['result' => true, 'token' => $jwt, 'user' => $payload];
    }

    public function changePassword(Request $request)
    {
        $current_password = $request->input('current_password');
        $new_password = $request->input('new_password');
        $confirm_password = $request->input('confirm_password');

        if ($new_password != $confirm_password) {
            return ['result' => false, 'message' => 'Password baru tidak sama'];
        }

        try {
            $user = User::firstOrFail();

            if (!Hash::check($current_password, $user->password)) {
                return response()->json(['message' => 'Password lama tidak sama'], 422);
            }

            $user->update(['password' => Hash::make($new_password)]);

            return ['result' => 'success'];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $er) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
    }
}
