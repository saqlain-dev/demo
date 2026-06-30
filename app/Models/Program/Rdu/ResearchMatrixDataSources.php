<?php

namespace App\Models\Program\Rdu;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchMatrixDataSources extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];
    public function DataSourceId(): BelongsTo
    {
        return  $this->belongsTo(TypeValue::class,'data_source_id')->select(['id','name']);
    }

    public function DataAvailability(): BelongsTo
    {
        return  $this->belongsTo(TypeValue::class,'data_availability')->select(['id','name']);
    }
}
