<?php

namespace App\Models\Program\Rdu;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchMatrixResearchOutput extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function ResearchOutputId(): BelongsTo
    {
        return  $this->belongsTo(TypeValue::class,'research_output_id')->select(['id','name']);
    }
    public function ResearchOutputPlaceId(): BelongsTo
    {
        return  $this->belongsTo(TypeValue::class,'research_output_place_id')->select(['id','name']);
    }
}
