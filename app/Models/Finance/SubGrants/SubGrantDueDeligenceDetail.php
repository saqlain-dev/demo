<?php

namespace App\Models\Finance\SubGrants;

use App\Models\Employee;
use App\Models\Finance\Grants\Nofo;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubGrantDueDeligenceDetail extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function SubGrantDueDeligenceId():BelongsTo
    {
        return $this->belongsTo(SubGrantDueDeligence::class, 'sub_grant_due_deligence_id');
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
