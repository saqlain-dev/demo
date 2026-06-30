<?php

namespace App\Models\Admin\Invoice;

use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceAudit extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'verified_by');
    }
}
