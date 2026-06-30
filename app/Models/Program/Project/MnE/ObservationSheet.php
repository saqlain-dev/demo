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

class ObservationSheet extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function TypeOfActivity(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'type_of_activity','id')->select(['id','name']);
    }

    public function MneObservations():HasMany
    {
        return $this->hasMany(MneObservation::class,'observation_sheet_id');
    }

    public function MneOfficerId():BelongsTo
    {
        return $this->belongsTo(Employee::class,'mne_officer_id');
    }

    public function DistrictId(): belongsTo
    {
        return $this->belongsTo(District::class,'district_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
