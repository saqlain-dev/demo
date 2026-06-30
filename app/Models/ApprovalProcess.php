<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalProcess extends Model
{
    use HasFactory, LogEvents, SoftDeletes;
    protected $guarded=['id'];
    public function processName(): BelongsTo
    {
        return $this->belongsTo(ApprovalProcessName::class,'approval_process_id');
    }
}
