<?php

namespace App\Models\Program\Rdu;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchMatrixResources extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function AllocatedProgramResources(): BelongsTo
    {
        return  $this->belongsTo(TypeValue::class,'allocated_program_resources')->select(['id','name']);
    }

    public function ResourcesAvailability(): BelongsTo
    {
        return  $this->belongsTo(TypeValue::class,'resources_availability')->select(['id','name']);
    }
}
