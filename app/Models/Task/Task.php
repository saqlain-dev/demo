<?php

namespace App\Models\Task;

use App\Models\Employee;
use App\Models\Lead;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function assignTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'assign_to');
    }

    public function taskStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'task_status');
    }

    public function taskPriority(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'task_priority');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class,'lead_id');
    }
}
