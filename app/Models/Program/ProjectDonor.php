<?php

namespace App\Models\Program;

use App\Traits\LogEvents;
use App\Models\Donar\DonarProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Program\Project\ProjectProfile;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProjectDonor extends Model
{
    use LogEvents;
    public function donorDetail(): BelongsTo
    {
        return $this->belongsTo(DonarProfile::class,'donor_id');
    }

    public function projectDetail(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'project_id');
    }

}
