<?php

namespace App\Models;

use App\Models\Quotation\Quotation;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function customerType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'customer_type');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class,'country_id');
    }

    public function customerContact(): HasMany
    {
        return $this->hasMany(CustomerContact::class,'customer_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    public function customerQuotation(): HasMany
    {
        return $this->hasMany(Quotation::class,'customer_id');
    }
}
