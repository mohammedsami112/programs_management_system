<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

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

        $userSpecifications = explode(',', User::find(Auth::user()->id)->specification);
        $searchSpecifications = explode('-', $userSpecifications[collect($userSpecifications)->search(function ($item, $key) use ($selectedSpecification) {
            return explode('-', $item)[0] == $selectedSpecification;
        })]);

        if ($searchSpecifications == "") {
            return false;
        }

        return explode('+', $searchSpecifications[1]);
    }
}
