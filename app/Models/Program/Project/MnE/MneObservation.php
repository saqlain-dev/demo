<?php

namespace App\Models\Program\Project\MnE;

use App\Models\District;
use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MneObservation extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function TypeOfRedFlag(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'type_of_red_flag','id')->select(['id','name']);
    }

    public function Priority(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'priority','id')->select(['id','name']);
    }
    public function ObservationSheetId():BelongsTo
    {
        return $this->belongsTo(ObservationSheet::class,'observation_sheet_id');
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function ProgrammaticResponses():HasMany
    {
        return $this->hasMany(ObservationProgrammaticResponse::class,'observation_id');
    }
}
