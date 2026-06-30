<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalProcessList extends Model
{
    use HasFactory, LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class,'designation_id');
    }


    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
