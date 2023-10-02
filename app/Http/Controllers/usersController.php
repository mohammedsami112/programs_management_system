<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class usersController extends Controller
{



    // Create Users
    public function create(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required|unique:users,username',
            'avatar' => 'mimes:jpeg,png,jpg',
            'email' => 'required|unique:users,email',
            'password' => 'required|confirmed',
            'country' => 'required',
            'city' => 'required',
            'permission' => 'required|exists:permissions,id',
        ], [
            // Name
            'name.required' => 'حقل الاسم مطلوب',

            // Username
            'username.required' => 'حقل اسم المستخدم مطلوب',
            'username.unique' => 'اسم المستخدم مستخدم من قبل',

            // Avatar
            'avatar.mimes' => 'امتداد الصورة يجب ان يكون :mimes',

            // Email
            'email.required' => 'حقل البريد الالكتروني مطلوب',
            'email.unique' => 'البريد الالكتروني مستخدم من قبل',

            // Password
            'password.required' => 'حقل كلمة السر مطلوب',

            // Country
            'country.required' => 'حقل البلد مطلوب',

            // City
            'city.required' => 'حقل المدينة مطلوب',

            // Permissions
            'permission.required' => 'حقل الصلحيات مطلوب',
            'permission.exists' => 'الصلحية المختارة غير موجودة',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        // Upload Avatar
        $avatar = null;
        if (isset($request->avatar)) {
            $avatar = $request->file('avatar')->store('users_avatar', 'public');
        }

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'avatar' => $avatar,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country' => $request->country,
            'city' => $request->city,
            'permission' => $request->permission,
            'leader' => Auth::user()->id
        ]);

        return $this->sendResponse(null, 'تم انشاء المسخدم بنجاح',);
    }
}
