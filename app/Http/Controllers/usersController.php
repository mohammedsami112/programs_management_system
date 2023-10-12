<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class usersController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$this->permission('users_view')) {
                abort(403);
            }
            return $next($request);
        });
    }

    // Users Filters
    public function usersFilters()
    {
        $permissions = Permission::select(['id', 'title'])->get()->makeHidden(['users_count']);

        $data = [
            'permissions' => $permissions,
        ];

        return $this->sendResponse($data);
    }

    // Get Users
    public function getUsers(Request $request)
    {
        $users = User::when($request->trash == true, function ($query) {
            $query->onlyTrashed();
        })->when($request->search, function ($query, $search) {
            $query->where('name', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%");
        })->when($request->permission, function ($query, $permission) {
            $query->where('permission', '=', $permission);
        })->when($request->sort, function ($query, $sort) use ($request) {
            $column = $request->sort_column ? $request->sort_column : 'id';
            $query->orderBy($column, $sort);
        })->when($this->permission('users_his_users'), function ($query) {
            $query->where('leader', '=', Auth::user()->id);
        })->paginate($request->limit ? $request->limit : 10);

        // $users = User::all();

        return $this->sendResponse($users);
    }

    // Get User
    public function getUser($userId)
    {
        if (!$this->permission('users_read')) {
            abort(403);
        }

        $validate = Validator::make(['user_id' => $userId], ['user_id' => 'required|exists:users,id']);
        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $user = User::find($userId);

        return $this->sendResponse($user);
    }

    // Create Users
    public function create(Request $request)
    {

        if (!$this->permission('users_create')) {
            abort(403);
        }

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

    // Update Users
    public function update(Request $request)
    {

        if (!$this->permission('users_update')) {
            abort(403);
        }

        $validate = Validator::make($request->all(), [
            'item_id' => 'required|exists:users,id',
            'name' => 'required',
            'username' => 'required|unique:users,username,' . $request->item_id,
            'avatar' => 'mimes:jpeg,png,jpg',
            'email' => 'required|unique:users,email,' . $request->item_id,
            'password' => 'confirmed',
            'country' => 'required',
            'city' => 'required',
            'permission' => 'required|exists:permissions,id',
        ], [
            // Item Id
            'item_id.exists' => 'المستخدم غير موجود',

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

        $user = User::find($request->item_id);

        // Upload Avatar
        $avatar = $user->avatar;
        if (isset($request->avatar)) {
            if ($user->avatar != null) {
                $avatar_path = public_path() . "/storage/" . $user->avatar;
                unlink($avatar_path);
            }
            $avatar = $request->file('avatar')->store('users_avatar', 'public');
        }

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'avatar' => $avatar,
            'email' => $request->email,
            'password' => isset($request->password) ? Hash::make($request->password) : $user->password,
            'country' => $request->country,
            'city' => $request->city,
            'permission' => $request->permission,
        ]);

        return $this->sendResponse(null, 'تم تحديث المسخدم بنجاح',);
    }

    // Delete Users
    public function delete($userId)
    {
        if (!$this->permission('users_delete')) {
            abort(403);
        }

        $validate = Validator::make(['user_id' => $userId], ['user_id' => 'required|exists:users,id']);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $user = User::find($userId);

        $user->delete();

        return $this->sendResponse(null, 'تم حذف المستخدم بنجاح');
    }
}
