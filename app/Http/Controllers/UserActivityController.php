<?php

namespace App\Http\Controllers; 
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Response;
class UserActivityController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->input('user_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $data = UserActivity::with(['user' => ['employeeDetail' => ['designation', 'department'], 'userdesignation']])
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->when($dateFrom && $dateTo, function ($query) use ($dateFrom, $dateTo) {
                return $query->whereBetween('created_at', [
                    \Carbon\Carbon::parse($dateFrom)->startOfDay(),
                    \Carbon\Carbon::parse($dateTo)->endOfDay(),
                ]);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    
    public function logoutTrack(Request $request)
    {
        $user = $request->user(); 
        UserActivity::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event'      => 'logout',
            'created_at' => now(),
        ]); 
        $user->currentAccessToken()?->delete();
        return resp(1, 'Logout recorded!', null, Response::HTTP_OK); 
    }
}
