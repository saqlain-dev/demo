<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Division\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use function Symfony\Component\String\u;

class LoginController extends Controller
{
    public function login(Request $request)
    {

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::query()->where('email', $request->email)->where(['user_type'=>1, 'status'=>1])->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if the user has any permissions
        $permissions = $user->getAllPermissions();
        if ($permissions->isEmpty()) {
            return resp(0, 'The user does not have any permissions.', [], Response::HTTP_FORBIDDEN);
        }


        // Revoke all tokens...
        $user->tokens()->delete();

        $division=Division::query()
            ->whereHas('divisionEmployee',function ($query) use ($user) {
                $query->where('employee_id',$user->employee_id);
            })
            ->with('divisionEmployee')
            ->get();
        $user->division = $division->isNotEmpty() ? $division : null;

        $device    = substr($request->userAgent() ?? '', 0, 255);
        $expiresAt = $request->remember ? null : now()->addMinutes(config('session.lifetime'));

        /*return response()->json([
            'access_token' => $user->createToken($device, expiresAt: $expiresAt)->plainTextToken,
            'user' => $user,
        ], Response::HTTP_CREATED);*/

        $data = [
            'access_token' => $user->createToken($device, expiresAt: $expiresAt)->plainTextToken,
            'user' => $user->makeHidden(['roles','permissions']),
            'permissions' => $permissions->pluck('name'),
            'roles' => $user->roles->pluck('name','id'),
        ];

        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    public function logout(Request $request)
    {
        //auth()->user()->tokens()->delete();

        auth()->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function show()
    {
        dd(auth()->user());
    }
    public function abilities()
    {
        $data['permissions'] = auth()->user()->getAllPermissions()->pluck('name');
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
}
