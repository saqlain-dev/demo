<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorLogin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VendorLoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(VendorLogin $vendorLogin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendorLogin $vendorLogin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendorLogin $vendorLogin)
    {
        //
    }
    public function login(Request $request)
    {

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::query()->where('email', $request->email)->where('user_type',2)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $device    = substr($request->userAgent() ?? '', 0, 255);
        $expiresAt = $request->remember ? null : now()->addMinutes(config('session.lifetime'));

        /*return response()->json([
            'access_token' => $user->createToken($device, expiresAt: $expiresAt)->plainTextToken,
            'user' => $user,
        ], Response::HTTP_CREATED);*/
        $data = [
            'access_token' => $user->createToken($device, expiresAt: $expiresAt)->plainTextToken,
            'user' => $user,
        ];
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    public function chnageVendorPassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required'],
            'password' => ['required'],
        ]);
        try {

            $user = User::query()->where('id', auth()->user()->id)->where('user_type',2)->first();
            if (! $user || ! Hash::check($request->old_password, $user->password)) {
                throw ValidationException::withMessages([
                    'old_password' => ['The old password are incorrect.'],
                ]);
            }else{
                DB::beginTransaction();
                User::query()->where('id',$user->id)->update(array('password'=>bcrypt($request->password)));
                DB::commit();
                return resp('1', 'Password updated Successfully!', [], Response::HTTP_CREATED);
            }


        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function vendorSignup(Request $request)
    {
        $request->validate([
            'company_name' => 'required',
            'email_address' =>'required|email|unique:users,email',
            'password' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $vendor= Vendor::query()->create($this->input);
            if($vendor){
                $this->input['name']=$request->company_name;
                $this->input['email']=$request->email_address;
                $this->input['password']=bcrypt($request->password);
                $this->input['status']=1;
                $this->input['vendor_id']=$vendor->id;
                $this->input['user_type']=2;
                $user=User::query()->create( $this->input);
                
                $user->assignRole('Vendor');
            }
            DB::commit();
            return resp(1,'Successful!', $vendor,Response::HTTP_CREATED);

        }catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function logout(Request $request)
    {
        //auth()->user()->tokens()->delete();

        auth()->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
