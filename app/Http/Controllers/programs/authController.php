<?php

namespace App\Http\Controllers\programs;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramFile;
use App\Models\ProgramUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Crypto\Rsa\PrivateKey;
use Spatie\Crypto\Rsa\PublicKey;

class authController extends Controller
{
    public function login(Request $request)
    {
        $program = Program::where('api_token', '=', $request->header('api_token'))->first();
        $privateKey = PrivateKey::fromString($program->private_key);
        $publicKey = PublicKey::fromString($program->public_key);
        $data = json_decode($privateKey->decrypt(base64_decode($request->data)), true);
        $validate = Validator::make($data, [
            'username' => 'required',
            'password' => 'required'
        ], [
            'username.required' => 'Username Is Required',
            'password.required' => 'Password Is Required'
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', base64_encode($publicKey->encrypt($validate->errors())), 400);
        }

        if (!Auth::attempt(['username' => $data['username'], 'password' => $data['password']])) {
            return $this->sendError('Unauthorized', base64_encode($publicKey->encrypt(json_encode(['error' => 'Username Or Password Is Invalid']))), 401);
        }

        $user = Auth::user();
        $programUsers = ProgramUsers::where('program_id', '=', $program->id)->where('user_id', '=', $user->id)->count();

        if ($programUsers != 1) {
            return $this->sendError('Unauthorized', base64_encode($publicKey->encrypt(json_encode(['error' => 'Username Or Password Is Invalid']))), 401);
        }

        $programFiles = ProgramFile::where('program_id', '=', $program->id)->get();

        $success = [
            'token' => $user->createToken('accessToken', ['program'])->plainTextToken,
            'user' => $user,
            'programs' => $programFiles
        ];

        return $this->sendResponse(base64_encode($publicKey->encrypt(json_encode($success))), 'Login Successfully');
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->sendResponse(null, 'Logout Successfully');
    }
}
