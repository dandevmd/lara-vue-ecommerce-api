<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function updateCredentials(Request $request)
    {
        $user = auth()->user();
        $credentials = $request->all();

        if ($credentials['password']) {
            $credentials['password'] = bcrypt($credentials['password']);
        }

        $user->update($credentials);

        return response()->json([
            'success' => true,
            'updated' => $user
        ]);
    }
    public function deleteAccount(Request $request)
    {

        $user = auth()->user();
        $user->delete();
        return response()->json([
            'success' => true,
            'deleted' => $user
        ]);
    }

}