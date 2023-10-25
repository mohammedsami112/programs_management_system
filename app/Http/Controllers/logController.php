<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Crypto\Rsa\PrivateKey;
use Spatie\Crypto\Rsa\PublicKey;

class logController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(function ($request, $next) {
    //         if (!$this->permission('logs_view')) {
    //             abort(403);
    //         }
    //         return $next($request);
    //     });
    // }

    public function getLogs(Request $request)
    {
        if (!$this->permission('logs_view')) {
            abort(403);
        }

        $logs = Log::when($request->search, function ($query, $search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('users.username', 'LIKE', "%$search%");
            })->orWhereHas('program', function ($q) use ($search) {
                $q->where('programs.title', 'LIKE', "%$search%");
            })->orWhere('device_name', 'LIKE', "$search")->orWhere('address', 'LIKE', "%$search%")->orWhere('mac_address', 'LIKE', "%$search%");
        })->when($request->sort, function ($query, $sort) use ($request) {
            $column = $request->sort_column ? $request->sort_column : 'id';
            $query->orderBy($column, $sort);
        })->when(!$this->permission('logs_all'), function ($query) {
            $query->whereHas('program', function ($q) {
                $q->where('programs.creator', '=', Auth::user()->id);
            });
        })->paginate($request->limit ? $request->limit : 10);

        return $this->sendResponse($logs);
    }

    public function create(Request $request, $programId)
    {
        // if (!$this->permission('logs_create')) {
        //     abort(403);
        // }

        $program = Program::find($programId);
        $privateKey = PrivateKey::fromString($program->private_key);
        $publicKey = PublicKey::fromString($program->public_key);
        $data = json_decode($privateKey->decrypt(base64_decode($request->data)), true);

        $validate = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'program_id' => 'required|exists:programs,id',
            'device_name' => 'required',
            'address' => 'required|ip',
            'file' => 'required',
            'mac_address' => 'required|MacAddress',
            'motherboard' => 'required',
            'description' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', base64_encode($publicKey->encrypt($validate->errors())), 400);
        }

        Log::create([
            'user_id' => $data['user_id'],
            'program_id' => $data['program_id'],
            'device_name' => $data['device_name'],
            'address' => $data['address'],
            'file' => $data['file'],
            'mac_address' => $data['mac_address'],
            'motherboard' => $data['motherboard'],
            'description' => $data['description']
        ]);

        return $this->sendResponse(null, 'Action Added Successfully');
    }
}
