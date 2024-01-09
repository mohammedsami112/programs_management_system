<?php

namespace App\Http\Controllers;

use App\Models\General;
use App\Models\Program;
use App\Models\ProgramFile;
use App\Models\ProgramUsers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Crypto\Rsa\KeyPair;
use ZipArchive;

class programsController extends Controller
{


    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$this->permission('programs_view')) {
                abort(403);
            }
            return $next($request);
        });
    }

    // Get Programs
    public function getPrograms(Request $request)
    {

        $programs = Program::withCount(['users', 'files'])->with('users')->when($request->trash == true, function ($query) {
            $query->onlyTrashed();
        })->when($request->search, function ($query, $search) {
            $query->where('title', 'LIKE', "%$search%");
        })->when($request->sort, function ($query, $sort) use ($request) {
            $column = $request->sort_column ? $request->sort_column : 'id';
            $query->orderBy($column, $sort);
        })->when($this->permission('programs_his_programs') == true, function ($query) {
            $query->where('creator', '=', Auth::user()->id);
        })->when($this->specification('specific_programs_users'), function ($query, $data) {
            $query->orWhereIn('creator', $data);
        })->paginate($request->limit ? $request->limit : 10);

        if (!$this->permission('programs_access_keys')) {
            $programs->getCollection()->each(function ($program) {
                $program->makeHidden(['public_key', 'private_key', 'api_token']);
            });
        }

        return $this->sendResponse($programs);
    }

    // Create Programs
    public function create(Request $request)
    {

        if (!$this->permission('programs_create')) {
            abort(403);
        }

        $validate = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        [$privateKey, $publicKey] = (new KeyPair())->generate();
        // dd($privateKey, $publicKey);
        Program::create([
            'title' => $request->title,
            'creator' => Auth::user()->id,
            'public_key' => $publicKey,
            'private_key' => $privateKey,
            'api_token' => md5(rand(0, 1000000000000))
        ]);

        return $this->sendResponse(null, 'Program Created Successfully');
    }

    // Update Programs
    public function update(Request $request)
    {
        if (!$this->permission('programs_update')) {
            abort(403);
        }

        $validate = Validator::make($request->all(), [
            'item_id' => 'required|exists:programs,id',
            'title' => 'required'
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $program = Program::find($request->item_id);

        $program->update([
            'title' => $request->title
        ]);

        return $this->sendResponse(null, 'Program Updated Successfully');
    }

    // Delete Programs
    public function delete($programId)
    {
        if (!$this->permission('programs_delete')) {
            abort(403);
        }

        $validate = Validator::make(['program_id' => $programId], ['program_id' => 'required|exists:programs,id']);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $program = Program::find($programId);

        $program->delete();

        return $this->sendResponse(null, 'Program Deleted Successfully');
    }

    // Force Delete Programs
    public function forceDelete($programId)
    {
        if (!$this->permission('programs_force_delete')) {
            abort(403);
        }

        $validate = Validator::make(['program_id' => $programId], ['program_id' => 'required|exists:programs,id']);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $program = Program::withTrashed()->find($programId);
        $programUsers = ProgramUsers::where('program_id', '=', $programId)->withTrashed()->forceDelete();

        $program->forceDelete();

        return $this->sendResponse(null, 'Program Permanently Deleted Successfully');
    }

    // Regenerate Keys
    public function regenerateKeys(Request $request)
    {
        if (!$this->permission('programs_access_keys')) {
            abort(403);
        }
        $validate = Validator::make($request->all(), [
            'item_id' => 'required|exists:programs,id',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $program = Program::find($request->item_id);

        [$privateKey, $publicKey] = (new KeyPair())->generate();

        $program->update([
            'public_key' => $publicKey,
            'private_key' => $privateKey,
        ]);

        return $this->sendResponse(null, 'Keys Regenerated Successfully');
    }

    // Add Users To Program
    public function addUsers(Request $request)
    {
        if (!$this->permission('programs_add_users')) {
            abort(403);
        }

        $validate = Validator::make($request->all(), [
            'program_id' => 'required|exists:programs,id',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $programUsersCount = ProgramUsers::where('program_id', '=', $request->program_id)->where('user_id', '=', $request->user_id)->count();

        if ($programUsersCount >= 1) {
            return $this->sendError('This User Has Been Added Before', [], 400);
        }

        ProgramUsers::create([
            'program_id' => $request->program_id,
            'user_id' => $request->user_id,
            'max_sessions' => $request->max_sessions
        ]);

        return $this->sendResponse(null, 'User Added To Program Successfully');
    }

    // Delete Users From Program
    public function deleteUsers(Request $request)
    {
        if (!$this->permission('programs_delete_users')) {
            abort(403);
        }

        $validate = Validator::make($request->all(), [
            'program_id' => 'required|exists:programs,id',
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->errors(), 400);
        }

        $programUser = ProgramUsers::where('program_id', '=', $request->program_id)->where('user_id', '=', $request->user_id)->first();
        $programUser->forceDelete();

        return $this->sendResponse(null, 'User Deleted From Program Successfully');
    }

    // Restore Programs
    public function restore($programId)
    {
        if (!$this->permission('programs_restore')) {
            abort(403);
        }

        $validate = Validator::make(['program_id' => $programId], ['program_id' => 'required|exists:programs,id']);

        if ($validate->fails()) {
            return $this->sendError('Validation Error', $validate->messages(), 400);
        }

        $program = Program::where('id', '=', $programId)->withTrashed();
        $program->restore();

        return $this->sendResponse(null, 'Program Restored Successfully');
    }

    // Users List
    public function usersList()
    {

        $users = User::when($this->permission('users_his_users'), function ($query) {
            $query->where('leader', '=', Auth::user()->id);
        })->without('permission')->select(['id', 'name'])->get();

        return $this->sendResponse($users);
    }

    // Upload Files
    public function uploadProgramFiles(Request $request)
    {
        if (!$this->permission('programs_upload_files')) {
            abort(403);
        }

        // Upload & Extract Zip File

        $zip = new ZipArchive();

        $status = $zip->open($request->file('file')->getRealPath());

        if ($status !== true) {
            return $this->sendError('Error', $status, 400);
        }

        $extractPath = storage_path('app/public/programs_files/' . $request->item_id . '/');

        if (!File::exists($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }
        $zip->extractTo($extractPath);
        $zip->close();

        // Get Code From Files And Store It In Database

        $programFiles  = Storage::allFiles('public/programs_files/' . $request->item_id);
        foreach ($programFiles as $file) {
            $fileContent = Storage::get($file);
            ProgramFile::create([
                'program_id' => $request->item_id,
                'file_name' => basename($file),
                'file_code' => $fileContent
            ]);
        }

        // Delete Extract Path
        File::deleteDirectory($extractPath);


        return $this->sendResponse(null, 'Files Uploaded & Store Successfully');
    }

    // Get General Keys
    public function getGeneralKeys()
    {
        if (!$this->permission('programs_general_keys')) {
            abort(403);
        }

        $generalKeys = General::first();

        return $this->sendResponse($generalKeys);
    }
}
