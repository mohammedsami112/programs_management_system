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
        $users = User::when($this->permission('users_his_users'), function ($query) {
            $query->where('leader', '=', Auth::user()->id);
        })->count();

        $onlineUsers = AccessTokens::select('tokenable_id')->when($this->permission('users_his_users'), function ($query) {
            $query->whereHas('user', function ($query) {
                $query->where('users.leader', '=', Auth::user()->id);
            });
        })->distinct()->count('tokenable_id');

        $programs = Program::when($this->permission('programs_his_programs'), function ($query) {
            $query->where('creator', '=', Auth::user()->id);
        })->count();

        $data = [
            'users' => $users,
            'online_users' => $onlineUsers,
            'programs' => $programs
        ];

        return $this->sendResponse($data);
    }
}
