<?php

namespace App\Models;

use App\Models\Admin\ProcurementDetail;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventManagementDetail extends Model
{
    use SoftDeletes, HasFactory, LogEvents;

    protected $guarded = ['id'];

    public function eventManagement()
    {
        return $this->belongsTo(EventManagement::class, 'event_management_id');
    }

    public function roomType()
    {
        return $this->belongsTo(TypeValue::class, 'room_type_id');
    }

    public function seatingArrangement()
    {
        return $this->belongsTo(TypeValue::class, 'seating_arrangement_id');
    }

    public function boardType()
    {
        return $this->belongsTo(TypeValue::class, 'board_type_id');
    }

    public function procurementDetails()
    {
        return $this->belongsTo(ProcurementDetail::class, 'procurement_details_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected $casts = [
        'other' => 'string',
    ];
}
