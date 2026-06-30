<?php

namespace App\Models\Program\Project\MnE;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MnePlanGoal extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    protected $casts = [
        'disaggregates' => 'array',
        'mne_tools' => 'array',
        'data_collection_freq' => 'array',
        'data_reporting_freq' => 'array',
        'required_movs' => 'array',
    ];
}
