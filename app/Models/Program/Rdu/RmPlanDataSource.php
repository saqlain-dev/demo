<?php

namespace App\Models\Program\Rdu;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RmPlanDataSource extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    protected $with = ['RmDataSource','RmDataAvailability'];

    public function RmDataSource(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'rm_data_source');
    }
    public function RmDataAvailability(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'rm_data_availability');
    }
}
