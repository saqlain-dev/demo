<?php

namespace App\Models\Program\Project\MnE;

use App\Models\Employee;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObservationProgrammaticResponse extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];
    public function ObservationId(): BelongsTo
    {
        return $this->belongsTo(MneObservation::class,'observation_id','id');
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
