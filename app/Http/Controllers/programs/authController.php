<?php

namespace App\Http\Controllers\programs;

use App\Http\Controllers\Controller;
use App\Models\AccessTokens;
use App\Models\General;
use App\Models\Program;
use App\Models\ProgramFile;
use App\Models\ProgramUsers;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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
        $programsKeys = [
            'public_key' => $program->public_key,
            'private_key' => $program->private_key,
            'jwt_signature' => $program->api_token,
        ];

        $user = Auth::user();
        $programUsers = ProgramUsers::where('program_id', '=', $program->id)->where('user_id', '=', $user->id);

        if ($programUsers->count() != 1 || AccessTokens::where('tokenable_id', '=', $user->id)->where('program_id', '=', $program->id)->count() >= $programUsers->first()->max_sessions) {
            return $this->sendError('Unauthorized', $this->dataEncryption(['error' => 'Username Or Password Is Invalid'], $programsKeys), 401);
        }

        $programFiles = ProgramFile::where('program_id', '=', $program->id)->get();

        $success = [
            'token' => $user->createToken('accessToken', ['program'], $program->id)->plainTextToken,
            'user' => $user,
            'program_files' => $programFiles
        ];

        return $this->sendResponse($this->dataEncryption($success, $programsKeys), 'Login Successfully');
    }

    public function generalLogin(Request $request)
    {
        $generalKeys = General::first();

        $data = collect($this->dataDecryption($request->data, $generalKeys))->toArray();

        $validate = Validator::make($data, [
            'username' => 'required',
            'password' => 'required'
        ], [
            'username.required' => 'Username Is Required',
            'password.required' => 'Password Is Required'
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', JWT::encode(collect($validate->errors())->toArray(), $generalKeys->private_key, 'RS256'), 400);
        }

        if (!Auth::attempt(['username' => $data['username'], 'password' => $data['password']])) {
            return $this->sendError('Unauthorized',  JWT::encode(['error' => 'Username Or Password Is Invalid'], $generalKeys->private_key, 'RS256'), 401);
        }

        $user = Auth::user();
        $userPrograms = ProgramUsers::where('user_id', '=', $user->id)->without('user')->with(['program' => function ($query) {
            $query->select(['id', 'title', 'api_token']);
        }])->get();

        $success = [
            'token' => $user->createToken('accessToken', ['generalPrograms'])->plainTextToken,
            'programs' => $userPrograms
        ];

        return $this->sendResponse($this->dataEncryption($success, $generalKeys));
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->sendResponse(null, 'Logout Successfully');
    }
}
