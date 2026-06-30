<?php

namespace App\Models\Finance\Budget;

use App\Models\Admin\Location;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetCategory extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function subCategory(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public static function getBudgetCategory(): Collection
    {
        return BudgetCategory::query()->with('subCategory')->where('parent_id','0')->get();
    }
}
