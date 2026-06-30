<?php

namespace App\Models\ErpActivity;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpActivity extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded=['id'];

    public function activityable(): MorphTo
    {
        return $this->morphTo();
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'performed_by');
    }

    public function activityState(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'activity_state');
    }
    public function activityType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'activity_type');
    }

    public function activityAttachments(): HasMany
    {
        return $this->hasMany(ErpActivityAttachment::class,'erp_activity_id');
    }
}
