<?php

namespace App\Models\Finance\ChartOfAccount;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccountClass extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function HeadClassId(): BelongsTo
    {
        return $this->belongsTo(HeadClass::class, 'head_class_id');
    }
}
