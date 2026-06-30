<?php

namespace App\Models\SalesTeam;

use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesTeamEmployee extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
