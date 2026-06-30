<?php

namespace App\Models\Admin\Fleet;

use App\Models\Program\Project\ProjectProfile;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelRequest extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function FuelConsumption():HasMany
    {
        return $this->HasMany(FuelConsumption::class,'fuel_request_id');
    }

    public function ProjectId(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'project_id')->select('id','project_name','created_by','updated_by');
    }
    public function VehicleId():BelongsTo
    {
        return $this->belongsTo(Vehicle::class,'vehicle_id');
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
