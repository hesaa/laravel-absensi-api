<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $_data = $request->only('email', 'password');

        $validate = Validator::make($_data, [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ])->errors()->all();

        if ($validate) {
            return $this->retrunScema($_data, 400, $validate);
        }


        $token = Auth::attempt($_data);

        if (!$token) {
            $status = 401;
            return $this->retrunScema($_data, $status, ['Email / Pasword invalid.']);
        }

        $user = Auth::user();

        $login = [
            'user' => $user,
            'auth' => [
                'token' => $token,
                'type' => 'Bearer'
            ]
        ];
        return $this->retrunScema($login, 200);
    }

    public function register(Request $request)
    {
        $_data = $request->only('name', 'email', 'password');

        $validate = Validator::make($_data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
        ])->errors()->all();

        if ($validate) {
            return $this->retrunScema($_data, 400, $validate);
        }

        $store = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($store);
        $register = [
            'user' => $store,
            'auth' => [
                'token' => $token,
                'type' => 'Bearer',
            ]
        ];
        return $this->retrunScema($register, 201);
    }

    public function logout()
    {
        Auth::logout();
        return $this->retrunScema([
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return $this->retrunScema([
            'user' => Auth::user(),
            'auth' => [
                'token' => Auth::refresh(),
                'type' => 'Bearer',
            ]
        ]);
    }

    public function me()
    {
        return $this->retrunScema([
            'user' => Auth::user(),
        ]);
    }
}
