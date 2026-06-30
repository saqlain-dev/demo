<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorDocuments extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];
    public function document(): BelongsTo
    {
        return $this->belongsTo(Documents::class,'document_id');
    }
}
