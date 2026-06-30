<?php

namespace App\Models\Finance\ChartOfAccount;

use App\Models\Program\Project\ProjectProfile;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeadClass extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function ProjectId():BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'project_id','id');
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
