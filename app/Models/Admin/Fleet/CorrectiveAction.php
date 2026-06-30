<?php

namespace App\Models\Admin\Fleet;

use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorrectiveAction extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];
    public function PersonResponsible():BelongsTo
    {
        return $this->belongsTo(Employee::class,'person_responsible')->select('id','name','employee_no');
    }
}
