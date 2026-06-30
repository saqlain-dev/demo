<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function commentable()
    {
        return $this->morphTo();
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select('id','name');
    }
}
