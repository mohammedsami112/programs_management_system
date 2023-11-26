<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Spatie\Crypto\Rsa\PrivateKey;
use Spatie\Crypto\Rsa\PublicKey;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function sendResponse($result, $message = 'success')
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message
        ];

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'data' => $errorMessages,
            'message' => $error
        ];

        return response()->json($response, $code);
    }

    public function permission($permission)
    {
        $permissionData = Permission::find(Auth::guard('sanctum')->user()->permission);
        if ($permissionData == null) {
            return false;
        }
        $permissions = explode(',', $permissionData->permissions);

        return in_array($permission, $permissions);
    }

    public function specification($selectedSpecification)
    {
        $user = User::find(Auth::user()->id)->specification;
        if ($user == null) {
            return false;
        }
        $userSpecifications = explode(',', $user);
        $searchSpecifications = explode('-', $userSpecifications[collect($userSpecifications)->search(function ($item, $key) use ($selectedSpecification) {
            return explode('-', $item)[0] == $selectedSpecification;
        })]);

        if ($searchSpecifications == "") {
            return false;
        }

        return explode('+', $searchSpecifications[1]);
    }

    public function dataEncryption($data, $keys)
    {
        $privateKey = PrivateKey::fromString($keys['private_key']);
        $publicKey = PublicKey::fromString($keys['public_key']);
        $signature = $keys['jwt_signature'];

        $jwtDataArray = str_split(JWT::encode($data, $signature, 'HS256'), 200);
        $jwtData = [];

        foreach ($jwtDataArray as $jwt) {
            $jwtData[] = base64_encode($publicKey->encrypt($jwt));
        }
        return $jwtData;
    }

    public function dataDecryption($data, $keys)
    {

        $privateKey = PrivateKey::fromString($keys['private_key']);
        $publicKey = PublicKey::fromString($keys['public_key']);
        $signature = $keys['jwt_signature'];


        $rsaData = $privateKey->decrypt(base64_decode($data));

        return JWT::decode($rsaData, new Key($signature, 'HS256'));
    }
}
