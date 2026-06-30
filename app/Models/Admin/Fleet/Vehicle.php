<?php

namespace App\Models\Admin\Fleet;

use App\Models\Program\Project\ProjectProfile;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes, HasFactory, LogEvents;

    protected $guarded = ['id'];

    public function VehicleType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'vehicle_type','id')->select(['id','name']);
    }

    public function ProjectId(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'project_id')->select('id','project_name','created_by','updated_by');
    }

    public function assignments()
    {
        return $this->hasMany(AssignVehicle::class, 'vehicle_id', 'id');
    }
}
