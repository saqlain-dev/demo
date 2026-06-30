<?php

namespace App\Models\Admin\Asset;

use App\Models\Employee;
use App\Models\ItemSubCategory;
use App\Models\Program\Project\ProjectProfile;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(ItemSubCategory::class, 'asset_category');
    }
    public function projectId(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class, 'project_id');
    }
    public function handoverTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'handover_to');
    }
}
