<?php

namespace App\Http\Controllers\programs;

use App\Http\Controllers\Controller;
use App\Models\AccessTokens;
use App\Models\General;
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
    public function programLogin(Request $request)
    {
        $program = Program::where('api_token', '=', $request->header('api_token'))->first();
        $privateKey = PrivateKey::fromString($program->private_key);
        $publicKey = PublicKey::fromString($program->public_key);
        //$data = json_decode($privateKey->decrypt(base64_decode($request->data)), true);


        $user = Auth::user();
        $programUsers = ProgramUsers::where('program_id', '=', $program->id)->where('user_id', '=', $user->id);

        if ($programUsers->count() != 1 || AccessTokens::where('tokenable_id', '=', $user->id)->where('program_id', '=', $program->id)->count() >= $programUsers->first()->max_sessions) {
            return $this->sendError('Unauthorized', base64_encode($publicKey->encrypt(json_encode(['error' => 'Username Or Password Is Invalid']))), 401);
        }

        $programFiles = ProgramFile::where('program_id', '=', $program->id)->get();

        $success = [
            'token' => $user->createToken('accessToken', ['program'], $program->id)->plainTextToken,
            'user' => $user,
            'program_files' => $programFiles
        ];

        return $this->sendResponse(base64_encode($publicKey->encrypt(json_encode($success))), 'Login Successfully');
    }

    public function generalLogin(Request $request)
    {
        $generalKeys = General::first();
        $privateKey = PrivateKey::fromString($generalKeys->private_key);
        $publicKey = PublicKey::fromString($generalKeys->public_key);

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
        $userPrograms = ProgramUsers::where('user_id', '=', $user->id)->without('user')->with(['program' => function ($query) {
            $query->select(['id', 'title', 'api_token']);
        }])->get();

        $success = [
            'token' => $user->createToken('accessToken', ['generalPrograms'])->plainTextToken,
            'programs' => $userPrograms
        ];

        return $this->sendResponse(base64_encode($publicKey->encrypt(json_encode($success))), 'Login Successfully');
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->sendResponse(null, 'Logout Successfully');
    }
}
