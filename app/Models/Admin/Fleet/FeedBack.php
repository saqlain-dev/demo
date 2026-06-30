<?php

namespace App\Models\Admin\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Fleet\FleetFeedBack;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeedBack extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    // public function fleetFeedBack():BelongsTo
    // {
    //     return $this->belongsTo(FleetFeedBack::class, 'id','feed_back_id');
    // }

    public function fleetFeedBacks()
    {
        return $this->hasMany(FleetFeedBack::class);
    }
}
