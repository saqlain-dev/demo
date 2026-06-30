<?php

namespace App\Models\Admin\Fleet;

use App\Models\Admin\Procurement;
use App\Models\Admin\ProcurementDetail;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleRequestDetail extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function VehicleRequestId():BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class,'vehicle_request_id');
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

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'procurement_id');
    }

    public function procurementDetails(): BelongsTo
    {
        return $this->belongsTo(ProcurementDetail::class, 'procurement_details_id');
    }
}
