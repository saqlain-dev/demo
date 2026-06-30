<?php

namespace App\Models\RFQ;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\RFP\Rfp;
use App\Models\Supplier\Supplier;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function rfqStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'rfq_status');
    }

    public function rfqDetail(): HasMany
    {
        return $this->hasMany(RfqItem::class,'rfq_id');
    }

    public function rfp(): BelongsTo
    {
        return $this->belongsTo(Rfp::class,'rfp_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }
}
