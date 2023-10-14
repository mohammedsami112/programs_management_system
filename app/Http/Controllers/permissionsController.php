<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class permissionsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$this->permission('permissions_view')) {
                abort(403);
            }
            return $next($request);
        });
    }

    // Get Permissions
    public function getPermissions(Request $request)
    {
        $permissions = Permission::when($request->trash == true, function ($query) {
            $query->onlyTrashed();
        })->when($request->search, function ($query, $search) {
            $query->where('title', 'LIKE', "%$search%");
        })->when($request->sort, function ($query, $sort) use ($request) {
            $column = $request->sort_column ? $request->sort_column : 'id';
            $query->orderBy($column, $sort);
        })->paginate($request->limit ? $request->limit : 10);

        return $this->sendResponse($permissions);
    }

    public function getPermission($permissionId)
    {
        if (!$this->permission('permissions_read')) {
            abort(403);
        }

        $validate = Validator::make(['permissions_id' => $permissionId], ['permissions_id' => 'required|exists:permissions,id']);
        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $permission = Permission::find($permissionId);

        return $this->sendResponse($permission);
    }

    // Add Permissions
    public function create(Request $request)
    {

        if (!$this->permission('permissions_create')) {
            abort(403);
        }

        $validate = Validator::make($request->all(), [
            'title' => 'required',
            'permissions' => 'required'
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }


        Permission::create([
            'title' => $request->title,
            'permissions' => $request->permissions
        ]);

        return $this->sendResponse(null, 'Permission Added Successfully');
    }

    // Update Permissions
    public function update(Request $request)
    {

        if (!$this->permission('permissions_update')) {
            abort(403);
        }

        $validate = Validator::make($request->all(), [
            'item_id' => 'required|exists:permissions,id',
            'title' => 'required',
            'permissions' => 'required'
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $permission = Permission::find($request->item_id);
        $permission->update([
            'title' => $request->title,
            'permissions' => $request->permissions
        ]);

        return $this->sendResponse(null, 'Permission Updated Successfully');
    }

    // Delete Permissions
    public function delete($permissionId)
    {
        if (!$this->permission('permissions_delete')) {
            abort(403);
        }

        $validate = Validator::make(['permission_id' => $permissionId], ['permission_id' => 'required|exists:permissions,id']);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->messages(), 400);
        }

        $permission = Permission::find($permissionId);
        $permission->delete();

        return $this->sendResponse(null, 'Permission Deleted Successfully');
    }

    // Restore Permissions
    public function restore($permissionId)
    {
        if (!$this->permission('permissions_restore')) {
            abort(403);
        }

        $validate = Validator::make(['permission_id' => $permissionId], ['permission_id' => 'required|exists:permissions,id']);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->messages(), 400);
        }

        $permission = Permission::where('id', '=', $permissionId)->withTrashed();
        $permission->restore();

        return $this->sendResponse(null, 'Permission Restored Successfully');
    }
}
