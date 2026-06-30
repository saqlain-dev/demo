<?php

namespace App\Models\Finance\Audit;

use App\Models\Comment;
use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use App\Models\Finance\Audit\AuditPlan;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObservationReport extends Model
{
    use SoftDeletes, LogEvents;
    
    protected $guarded = ['id'];

    public function ticketSchedule(): BelongsTo
    {
        return $this->belongsTo(TicketSchedule::class, 'ticket_schedule_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
  
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
