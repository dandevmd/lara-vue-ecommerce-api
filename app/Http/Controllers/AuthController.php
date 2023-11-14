<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if (!$data) {
                return response([
                    'error' => 'The Provided credentials are not correct'
                ], 422);
            }

            $data['password'] = bcrypt($data['password']);
            $user = User::create($data);

            Auth::login($user);
            // $user->sendEmailVerificationNotification();

            return response([
                'user' => new UserResource($user),
                'token' => $user->createToken('main')->plainTextToken
            ]);
        } catch (\Throwable $th) {
            return response([
                'error' => $th->getMessage()
            ], 422);
        }

    }
    public function login(Request $request)
    {
        //validate credentials 
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['boolean']

        ]);

        $remember = $credentials['remember'] ?? false;
        unset($credentials['remember']);

        if (!Auth::attempt($credentials, $remember)) {
            return response([
                'error' => 'The Provided credentials are not correct'
            ], 422);
        }

        $user = Auth::user();


        if (!$user->email_verified_at) {
            //return  $user->sendEmailVerificationNotification();
        }

        $token = $user->createToken('main')->plainTextToken;

        return response([
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }

    public function logout()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return response('', 204);
    }

    public function getUser(Request $request)
    {
        return new UserResource($request->user());
    }
}