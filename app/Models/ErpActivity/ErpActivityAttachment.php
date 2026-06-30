<?php

namespace App\Models\ErpActivity;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpActivityAttachment extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded=['id'];

    public function attachmentType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'attachment_type');
    }
}
