<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Province extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function TargetAreas(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
