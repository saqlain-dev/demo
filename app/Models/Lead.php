<?php

namespace App\Models;

use App\Models\ErpActivity\ErpActivity;
use App\Models\Inquiry\Inquiry;
use App\Models\Task\Task;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function gender(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'gender');
    }
    public function salutation(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'salutation');
    }

    public function leadStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'lead_status');
    }

    public function leadType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'lead_type');
    }
    public function leadRequestType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'lead_request_type');
    }
    public function leadOwner(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'lead_owner');
    }

    public function qualificationType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'qualification_type');
    }
    public function qualificationStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'qualification_status');
    }
    public function qualifiedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'qualified_by');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Company::class,'organization_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class,'lead_id');
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class,'inquiry_id');
    }
    public function activities()
    {
        return $this->morphMany(ErpActivity::class, 'activityable');
    }

    public function leadQualification(): HasOne
    {
        return $this->hasOne(LeadQualification::class,'lead_id');
    }

    public function leadActivity(): BelongsTo
    {
        return $this->belongsTo(ErpActivity::class,'activity_id');
    }
}
