<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class authController extends Controller
{

    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ], [
            'username.required' => 'Username Is Required',
            'password.required' => 'Password Is Required'
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            return $this->sendError('Unauthorized', ['error' => 'Username Or Password Is Invalid'], 401);
        }

        $user = Auth::user();
        $success = [
            'token' => $user->createToken('accessToken')->plainTextToken,
            'user' => $user,
        ];

        return $this->sendResponse($success, 'Login Successfully');
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return $this->sendResponse(null, 'Logout Successfully');
    }
}
