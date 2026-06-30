<?php

namespace App\Models\Communication;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignCommunicationEventTask extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(CommunicationEvent::class, 'event_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(CommunicationEventDetail::class, 'task_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_by')->select('id','name');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to')->select('id','name');
    }

}
