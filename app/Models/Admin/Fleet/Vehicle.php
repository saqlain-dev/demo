<?php

namespace App\Models\Admin\Fleet;

use App\Models\Program\Project\ProjectProfile;
use App\Models\TypeValue;
use App\Models\VehicleRecord;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    
    public function LogBooks()
    {
        return $this->hasMany(LogBook::class);
    }

    public function assignments()
    {
        return $this->hasMany(AssignVehicle::class, 'vehicle_id', 'id');
    }

    public function latestVehicleLog()
    {
        return $this->hasOne(LogBook::class, 'vehicle_id', 'id')->latestOfMany();
    }

    public function vehicleRecords() : HasMany
    {
        return $this->hasMany(VehicleRecord::class, 'vehicle_id', 'id');
    }

    public function assignedVehicleLogHistory() : HasMany
    {
        return $this->hasMany(AssignVehicleLog::class, 'vehicle_id');
    }
}
