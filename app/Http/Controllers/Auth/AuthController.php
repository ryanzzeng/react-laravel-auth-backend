<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\HttpResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['login','register','refresh']]);
    }

    public function me()
    {
        $data = Auth::guard('api')->user()->toArray();
        return HttpResponse::success($data, 'Query user information successfully');
    }

    public function register(Request $request)
    {
        $user = User::create([
             'email'    => $request->email,
             'username' => $request->username,
             'password' => $request->password,
         ]);
        
        $token = auth()->login($user);

        return $this->respondWithToken($token,$request->username);
    }

    public function login()
    {
        $credentials = request(['username', 'password']);

        if (! $token = auth('api')->attempt($credentials)) {
            throw new UnauthorizedHttpException('jwt-auth','Invalid Credentials');
        }

        return $this->respondWithToken($token,$credentials['username']);
    }

    public function logout()
    {
        auth('api')->logout();

        return HttpResponse::success(null, 'Log out successfully');
    }

    protected function respondWithToken($token,$username)
    {
        $data = [
            'username' => $username,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ];
        return HttpResponse::success($data, 'Get token successfully');
    }
}
