<?php

namespace App\Models;

use App\Models\Program\Project\ProjectProfile;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes, LogEvents;
    protected $guarded=['id'];

    public function projectDetail():BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'project_id');
    }
    public function themeName():BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'theme_id');
    }
    public function lessonCategory():BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'category_id');
    }
    public function provienceName():BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'province_id');
    }
}
