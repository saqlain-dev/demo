<?php

namespace App\Models\LearningLog;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningDetail extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function followedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'follow_up_required_by');
    }
    public function learningTheme(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'learning_theme');
    }
}
