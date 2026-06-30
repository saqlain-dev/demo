<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use App\Models\HeadOffice;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $users=User::with(['employeeDetail' => ['designation','department'],'userdesignation'])->get();
        return resp(1,'Successful!', $users,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $request->validate([
            'name' => 'required',
            'password' => 'required',
            //'employee_id' => 'required',
            'status' => 'required',
            //'designation_id' => 'required',
            'email' => 'required|email|unique:users,email',
        ]);

        $this->input['password']=bcrypt($request->password);
        $user=User::query()->create( $this->input);
        return resp(1,'Successful!', $user,Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $userDetail=User::with(['employeeDetail'])->findOrFail($id);
        return resp(1,'Successful!', $userDetail,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $request->validate([
            'name' => 'required',
            //'employee_id' => 'required',
            'status' => 'required',
            //'designation_id' => 'required',
            'email' => 'required|email|unique:users,email,' .$user->id. ',id',
        ]);
        if($request->has('password') && $this->input['password'] != "") {
            $this->input['password'] = bcrypt($request->password);
        }

        $updateUser=User::query()->where('id', $user->id)->update( $this->input);
        $userDetail=User::with(['employeeDetail'])->findOrFail($user->id);
        return resp(1,'Successful!', $userDetail,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $user->delete();
        return resp(1,'Successful!', [],Response::HTTP_CREATED);
    }
    public function searchEmp(Request $request){

        $query=Employee::query()->select('employees.*','hof.name as head_office_name','bof.name as baranch_office_name')->leftJoin('designations as d', 'd.id' , '=', 'employees.designation_id')->leftJoin('head_offices as hof', 'hof.id' , '=', 'employees.head_office_id')->leftJoin('branch_offices as bof', 'bof.id' , '=', 'employees.branch_office_id');

        if($request->has('name')){
            $query->Where('employees.name', 'like', '%' . $request->name . '%');
        }
        if($request->has('designation_id')){
            $query->Where('employees.designation_id',  $request->designation);
        }
        if($request->has('department')){
            $query->Where('employees.department_id',  $request->department);
        }
        if($request->has('head_office')){
            $query->Where('employees.head_office_id',  $request->head_office);
        }
        if($request->has('baranch_office')){
            $query->Where('employees.branch_office_id',  $request->baranch_office);
        }
        $employee=$query->get();
        /*$bindings = $query->getBindings();
        $sqlWithValues = vsprintf(str_replace('?', '%s', $employee), $bindings);
        dd($sqlWithValues);*/
        return resp(1,'Successful!', $employee,Response::HTTP_CREATED);
    }
    public function userDropdown(){
        $emplyeeIds=User::whereNotNull('employee_id')->pluck('employee_id');

        $data['headoffice']=HeadOffice::with('branches')->where('status',1)->get();
        $data['departments']=Type::getTypeValues('department-names');
        $data['designation']=Designation::all();
        $data['employeeList']=Employee::with(['department','designation'])->whereNotIn('id', $emplyeeIds)->get();;
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    public function saveSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|file',
            'user_id' => 'required|integer',
        ]);
        if ($request->hasFile('signature')){
            $responce = $this->saveFile($request, 'UserESignature');

            if ($responce) {
                $user=User::query()->where('id',$request->user_id)->first();
                if($user){
                    $user->signature=$responce;
                    $user->save();
                    $user->refresh();
                    return resp(1, 'Successful!', $user, Response::HTTP_OK);
                }else{
                    return resp(0, 'Failed to update record!', [], Response::HTTP_EXPECTATION_FAILED);
                }
            }else{
                return resp(0, 'File not upload!', [], Response::HTTP_EXPECTATION_FAILED);
            }
        }
    }
    public function saveFile($request,$folder){

        $file = $request->file('signature');
        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;

    }

    public function userActiveInactive(Request $request)
    {
        $request->validate([
            'status' => 'required|in:0,1',
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ]);
        try {
            DB::beginTransaction();

            $status = $request->status;
            $userIds = $request->users;


            User::whereIn('id', $userIds)->update(['status' => $status]);

            // Perform additional operations if activating
                if ($status == 1) {
                    $newPassword = 'HR_LAS_123_!@#)';
                    $hashed = bcrypt($newPassword);

                    $users = User::whereIn('id', $userIds)->get();
                    foreach ($users as $user) {
                        $user->password = $hashed;
                        $user->save();

                        dispatch(new \App\Jobs\SendEmailJob([
                            'to' => $user->email,
                            'subject' => 'Account Activated',
                            'body' => "
                                We are pleased to inform you that your account has been successfully activated.<br><br>
                                
                                For your initial login, please use the following credentials:<br>
                                Username: {$user->email}<br>
                                Password: HR_LAS_123_!@#)<br><br>
                                
                                <strong>:Warning:</strong><br>
                                For security reasons, please log in immediately and change your password.<br>
                                Do not share your password with anyone.<br>
                                If you encounter any issues accessing your account, kindly contact our support team at <a href='mailto:info@las.org.pk'>info@las.org.pk</a> or call +92-21-35634112-4.
                            "
                        ]));
                    }
                }
            DB::commit();
            return resp('1', 'Users updated successfully.', [], Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();
                return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
            }

    }
}
