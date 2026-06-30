<?php

namespace App\Models\Governance;

use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardResolutionApprovalCommittee extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function employeeDetail(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'board_member_id');
    }
}
