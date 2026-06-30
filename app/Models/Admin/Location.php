<?php

namespace App\Models\Admin;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function subLocations(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // New relation for racks
    public function racks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // New relation for aisles
    public function aisles(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public static function getLocations(): Collection
    {
        return Location::query()->with(['subLocations.racks.aisles'])->where('parent_id','0')->get();
    }

    public function inventory() :HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
