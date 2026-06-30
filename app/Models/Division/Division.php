<?php

namespace App\Models\Division;

use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function divisionEmployee(): HasMany
    {
        return $this->hasMany(DivisionEmployee::class,'division_id');
    }

    public function divisionHead(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'division_head_id');
    }
}
