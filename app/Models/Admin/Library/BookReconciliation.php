<?php

namespace App\Models\Admin\Library;

use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookReconciliation extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function ReconciliationDetail(): HasMany
    {
        return $this->hasMany(BookReconciliationDetail::class,'book_reconciliation_id');
    }

    public function ReconciliationType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'reconciliation_type')->select('id','name');
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
