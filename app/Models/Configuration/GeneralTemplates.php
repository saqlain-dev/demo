<?php

namespace App\Models\Configuration;

use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralTemplates extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function TemplateType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'template_type')->select(['id', 'name']);
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
