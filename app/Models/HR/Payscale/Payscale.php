<?php

namespace App\Models\HR\Payscale;

use App\Models\Designation;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payscale extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];


    public function SalaryRange(): HasMany
    {
        return $this->HasMany(SalaryRange::class,'payscale_id');
    }
    public function Grading(): BelongsTo
    {
        return $this->belongsTo(PayscaleGrading::class,'grading','id');
    }
    public function Level(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'level','id')->select(['id','name']);
    }
    public function position(): BelongsTo
    {
        return $this->belongsTo(Designation::class,'position','id');
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
