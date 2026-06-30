<?php

namespace App\Models\Questionnaire;

use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    protected $with = ['created_by','updated_by'];

    protected $casts = [
        'collection' => 'array'
    ];

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by')->select(['id','name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by')->select(['id','name']);
    }


}
