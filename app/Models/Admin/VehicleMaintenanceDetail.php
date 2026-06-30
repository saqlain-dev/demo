<?php

namespace App\Models\Admin;

use App\Models\Item;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleMaintenanceDetail extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(VehicleMaintenanceForm::class, 'parent_id');
    }

    public function natureOfWork(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'nature_of_work','id');
    }
}
