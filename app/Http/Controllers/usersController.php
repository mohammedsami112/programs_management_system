<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\ProgramUsers;
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
        // dd($this->permission(null, 'specific_users'));
        $users = User::where('id', '!=', Auth::user()->id)->when($request->trash == true, function ($query) {
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

    // Get Users List
    public function getUsersList()
    {
        $users = User::without('user_permission')->select(['id', 'name'])->when($this->permission('users_his_users'), function ($query) {
            $query->where('leader', '=', Auth::user()->id);
        })->get();

        return $this->sendResponse($users);
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
            'permission' => 'required|exists:permissions,id',
        ], [
            // Name
            'name.required' => 'Name Is Required',

            // Username
            'username.required' => 'Username Is Required',
            'username.unique' => 'Username Already Exists',

            // Avatar
            'avatar.mimes' => 'Avatar Type Should Be :mimes',

            // Email
            'email.required' => 'Email Is Required',
            'email.unique' => 'Email Already Exists',

            // Password
            'password.required' => 'Password Is Required',

            // Permissions
            'permission.required' => 'Permission Is Required',
            'permission.exists' => "Permission Does't Exist",
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
            'permission' => $request->permission,
            'leader' => Auth::user()->id
        ]);

        return $this->sendResponse(null, 'User Created Successfully',);
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
            'permission' => 'required|exists:permissions,id',
        ], [
            // Item Id
            'item_id.exists' => "User Doesn't Exist",


            // Name
            'name.required' => 'Name Is Required',

            // Username
            'username.required' => 'Username Is Required',
            'username.unique' => 'Username Already Exists',

            // Avatar
            'avatar.mimes' => 'Avatar Type Should Be :mimes',

            // Email
            'email.required' => 'Email Is Required',
            'email.unique' => 'Email Already Exists',

            // Password
            'password.required' => 'Password Is Required',

            // Permissions
            'permission.required' => 'Permission Is Required',
            'permission.exists' => "Permission Does't Exist",

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
            'permission' => $request->permission,
        ]);

        return $this->sendResponse(null, 'User Updated Successfully',);
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
        $programs = ProgramUsers::where('user_id', '=', $userId)->delete();

        $user->delete();

        return $this->sendResponse(null, 'User Deleted Successfully');
    }

    // Force Delete Users
    public function forceDelete($userId)
    {
        if (!$this->permission('users_force_delete')) {
            abort(403);
        }

        $validate = Validator::make(['user_id' => $userId], ['user_id' => 'required|exists:users,id']);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $user = User::withTrashed()->find($userId);
        $programs = ProgramUsers::where('user_id', '=', $userId)->withTrashed()->forceDelete();

        $user->forceDelete();

        return $this->sendResponse(null, 'User Permanently Deleted Successfully');
    }

    // Restore Users
    public function restore($userId)
    {
        if (!$this->permission('users_restore')) {
            abort(403);
        }

        $validate = Validator::make(['user_id' => $userId], ['user_id' => 'required|exists:users,id']);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $user = User::where('id', '=', $userId)->withTrashed();
        $programs = ProgramUsers::where('user_id', '=', $userId)->withTrashed()->restore();

        $user->restore();

        return $this->sendResponse(null, 'User Restored Successfully');
    }
}
