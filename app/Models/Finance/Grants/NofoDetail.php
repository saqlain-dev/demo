<?php

namespace App\Models\Finance\Grants;

use App\Http\Controllers\Api\V1\Finance\Grants\DueDelegenceDetailController;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NofoDetail extends Model
{

    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];
    public function NofoId():BelongsTo
    {
        return $this->belongsTo(Nofo::class, 'nofo_id');
    }

    public function DueDeligenceDetail(): BelongsTo
    {
        return $this->belongsTo(DueDelegenceDetail::class, 'nofo_detail_id');
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
