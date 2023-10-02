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
            'username.required' => 'حقل اسم المستخدم مطلوب',
            'password.required' => 'حقل كلمة السر مطلوب'
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            return $this->sendError('Unauthorized', ['error' => 'البريد الالكتروني او كلمة السر غير صحيح'], 401);
        }

        $user = Auth::user();
        $success = [
            'token' => $user->createToken('accessToken')->plainTextToken,
            'user' => $user,
        ];

        return $this->sendResponse($success, 'تم تسجيل الدخول بنجاح');
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return $this->sendResponse(null, 'تم تسجيل الخروج بنجاح');
    }
}
