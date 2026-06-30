<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventTask extends Model
{
    use HasFactory, SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function eventManagement()
    {
        return $this->belongsTo(EventManagement::class, 'event_management_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assignTo()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    public function flagStatus()
    {
        return $this->belongsTo(TypeValue::class, 'flag_status');
    }

    public function taskStatus()
    {
        return $this->belongsTo(TypeValue::class, 'task_status');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
