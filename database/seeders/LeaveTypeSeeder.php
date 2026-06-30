<?php

namespace Database\Seeders;

use App\Models\HR\LeaveType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $leaveTypes = [
            ['LeaveTypeID' => 1, 'Description' => 'Annual Leave', 'WithoutBalance' => 0, 'SandwichRule' => 0],
            ['LeaveTypeID' => 2, 'Description' => 'Medical Leaves', 'WithoutBalance' => 0, 'SandwichRule' => 0],
            ['LeaveTypeID' => 3, 'Description' => 'Other Leaves', 'WithoutBalance' => 1, 'SandwichRule' => 1],
            ['LeaveTypeID' => 4, 'Description' => 'Without Pay', 'WithoutBalance' => 1, 'SandwichRule' => 1],
            ['LeaveTypeID' => 5, 'Description' => 'Without Pay', 'WithoutBalance' => 1, 'SandwichRule' => 1],
            ['LeaveTypeID' => 6, 'Description' => 'Marriage Leaves', 'WithoutBalance' => 1, 'SandwichRule' => 1],
            ['LeaveTypeID' => 7, 'Description' => 'Marriage Leaves', 'WithoutBalance' => 1, 'SandwichRule' => 1],
            ['LeaveTypeID' => 8, 'Description' => 'On Duty', 'WithoutBalance' => 1, 'SandwichRule' => 0],
            ['LeaveTypeID' => 9, 'Description' => 'On Duty', 'WithoutBalance' => 1, 'SandwichRule' => 0],
            ['LeaveTypeID' => 10, 'Description' => 'On Duty', 'WithoutBalance' => 1, 'SandwichRule' => 0],
            ['LeaveTypeID' => 11, 'Description' => 'On Duty', 'WithoutBalance' => 1, 'SandwichRule' => 0],
            ['LeaveTypeID' => 12, 'Description' => 'On Duty', 'WithoutBalance' => 1, 'SandwichRule' => 0],
            ['LeaveTypeID' => 13, 'Description' => 'On Duty', 'WithoutBalance' => 1, 'SandwichRule' => 0],
            ['LeaveTypeID' => 14, 'Description' => 'On Duty', 'WithoutBalance' => 1, 'SandwichRule' => 0],
            ['LeaveTypeID' => 15, 'Description' => 'Maternity Leaves', 'WithoutBalance' => 0, 'SandwichRule' => 1],
            ['LeaveTypeID' => 16, 'Description' => 'Sick Leave', 'WithoutBalance' => 0, 'SandwichRule' => 0],
        ];

        //LeaveType::query()->insert($leaveTypes);
        foreach ($leaveTypes as $leaveType) {
            LeaveType::create($leaveType);
        }
        LeaveType::query()->whereIn('LeaveTypeID',[5,7,9,10,11,12,13,14])->delete();
    }
}
