<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckInSheet extends Model
{
    use HasFactory, LogEvents, SoftDeletes;
    protected $guarded=['id'];
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class,'activitie_id');
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by')->select(['id','name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by')->select(['id','name']);
    }
}
