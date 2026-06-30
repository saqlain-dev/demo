<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcknowledgementHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'model_id',
        'old_acknowledgement_date',
        'new_acknowledgement_date',
        'updated_by',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
