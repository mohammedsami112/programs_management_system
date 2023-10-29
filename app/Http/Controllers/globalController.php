<?php

namespace App\Http\Controllers;

use App\Models\AccessTokens;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class globalController extends Controller
{
    public function home()
    {
        $data = [];
        if ($this->permission("users_view")) {
            $users = User::when($this->permission('users_his_users'), function ($query) {
                $query->where('leader', '=', Auth::user()->id);
            })->when($this->permission(null, 'specific_users'), function ($query, $data) {
                $ids = explode('+', explode('-', $data)[1]);
                $query->whereIn('id', $ids);
            })->count();
            $data['users'] = $users;

            $onlineUsers = AccessTokens::select('tokenable_id')->when($this->permission('users_his_users'), function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->where('users.leader', '=', Auth::user()->id);
                });
            })->when($this->permission(null, 'specific_users'), function ($query, $data) {
                $ids = explode('+', explode('-', $data)[1]);
                $query->whereHas('user', function ($query) use ($ids) {
                    $query->whereIn('id', $ids);
                });
            })->where('abilities', '=', '["dashboard"]')->distinct()->count('tokenable_id');
            $data['online_users'] = $onlineUsers;
        }

        if ($this->permission('programs_view')) {
            $programs = Program::when($this->permission('programs_his_programs'), function ($query) {
                $query->where('creator', '=', Auth::user()->id);
            })->when($this->permission(null, 'specific_programs_users'), function ($query, $data) {
                $ids = explode('+', explode('-', $data)[1]);
                $query->whereIn('creator', $ids);
            })->count();
            $data['programs'] = $programs;
        }

        return $this->sendResponse($data);
    }
}
