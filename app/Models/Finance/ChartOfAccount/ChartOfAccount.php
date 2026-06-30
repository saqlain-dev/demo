<?php

namespace App\Models\Finance\ChartOfAccount;

use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class ChartOfAccount extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function AccountTypeId():BelongsTo
    {
        return  $this->belongsTo(TypeValue::class,'account_type_id')->select('id','name');
    }

    public function parent():BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class,'parent_id','id');
    }

    public function parent_id():BelongsTo

    {
        return $this->belongsTo(ChartOfAccount::class,'parent_id','id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }
    public function ClassId():HasMany
    {
        return $this->hasMany(ChartOfAccountClass::class,'chart_of_account_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    // Recursive function to load parents
    public function loadRecursiveParents()
    {
        $this->load('parent');
        if ($this->parent) {
            $this->parent->loadRecursiveParents();
        }
    }

    public function loadAllParents()
    {
        // Load the immediate parent first.
        $this->load('parent');

        // Get the parent model.
        $parent = $this->parent;

        // Loop to load all ancestors.
        while ($parent) {
            // Load the parent relationship for the current parent.
            $parent->load('parent');

            // Move up to the next ancestor.
            $parent = $parent->parent ?? null; // Ensure null safety.
        }
    }

}
