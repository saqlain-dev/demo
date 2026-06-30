<?php

namespace App\Models\SalesTeam;

use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesTeam extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function salesTeamEmployee(): HasMany
    {
        return $this->hasMany(SalesTeamEmployee::class,'sales_team_id');
    }

    public function salesTeamHead(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'sales_head_id');
    }
}
