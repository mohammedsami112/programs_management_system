<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class logController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$this->permission('logs_view')) {
                abort(403);
            }
            return $next($request);
        });
    }
    public function create(Request $request)
    {
        if (!$this->permission('logs_create')) {
            abort(403);
        }

        $validate = Validator::make($request->all(), [
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
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        Log::create([
            'user_id' => $request->user_id,
            'program_id' => $request->program_id,
            'device_name' => $request->device_name,
            'address' => $request->address,
            'file' => $request->file,
            'mac_address' => $request->mac_address,
            'motherboard' => $request->motherboard,
            'description' => $request->description
        ]);

        return $this->sendResponse(null, 'تم اضافة النشاط بنجاح');
    }
}
