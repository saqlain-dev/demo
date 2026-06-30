<?php

namespace App\Models\HR\Insurance;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeRelative extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(EmployeeInsurances::class);
    }

    public function relationId(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'relation_id');
    }

    public function fileType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'file_type');
    }
}
