<?php

namespace App\Models\LearningLog;

use App\Models\Comment;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningLog extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'department');
    }

    public function learningDetail(): HasMany
    {
        return $this->hasMany(LearningDetail::class,'learning_log_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
