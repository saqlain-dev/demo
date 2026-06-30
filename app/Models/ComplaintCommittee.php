<?php

namespace App\Models;

use App\Models\Configuration\GeneratedLetter;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintCommittee extends Model
{
    use LogEvents;
    protected $guarded=['id'];

    public function memeberDetail(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'member_id');
    }

    public function complaintLetter(): BelongsTo
    {
        return $this->belongsTo(GeneratedLetter::class,'nda_letter');
    }
}
