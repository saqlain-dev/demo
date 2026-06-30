<?php

namespace App\Models\Finance\Grants;

use App\Models\Employee;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantCloseOut extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function NofoId():BelongsTo
    {
        return $this->belongsTo(Nofo::class, 'nofo_id');
    }
    public function DraftBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'draft_by');
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
