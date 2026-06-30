<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchOffice extends Model
{
    use HasFactory, SoftDeletes, LogEvents;

    protected $guarded=['id'];
    public function headOffices():BelongsTo
    {
        return $this->belongsTo(HeadOffice::class,'head_office_id');
    }
}
