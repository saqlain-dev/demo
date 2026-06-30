<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerContact extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function customerContactStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'customer_contact_status');
    }
    public function salutation(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'salutation_id');
    }
    public function gender(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'gender_id');
    }
}
