<?php

namespace App\Models\Opportunity;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Configuration\ScaleRating;
use App\Models\Employee;
use App\Models\ErpActivity\ErpActivity;
use App\Models\Quotation\Quotation;
use App\Models\RFP\Rfp;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded=['id'];

    public function opportunityable(): MorphTo
    {
        return $this->morphTo();
    }
    public function opportunityType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'opportunity_type')->withDefault();
    }
    public function salesStage(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'sales_stage')->withDefault();
    }
    public function opportunityStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'opportunity_status')->withDefault();
    }
    public function opportunityOwner(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'opportunity_owner')->withDefault();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }



    public function rfp(): HasOne
    {
        return $this->hasOne(Rfp::class,'opportunity_id');
    }

    public function stage_rating(): BelongsTo
    {
        return $this->belongsTo(ScaleRating::class,'stage_rating')->withDefault();
    }

    public function opp_activities(): HasMany
    {
        return $this->hasMany(ErpActivity::class,'opportunity_id');
    }

    public function priority_level(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'priority_level')->withDefault();
    }
}
