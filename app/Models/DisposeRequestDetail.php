<?php

namespace App\Models;

use App\Models\Admin\ItemVariant;
use App\Models\Admin\Procurement;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\Program\Project\ProjectProfile;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisposeRequestDetail extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function disposeRequest(): BelongsTo
    {
        return $this->belongsTo(DisposeRequest::class,'dispose_request_id');
    }

    public function itemVariant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class);
    }

    public function rfqDetails(): HasMany
    {
        return $this->hasMany(PurchaseRequestRfqDetail::class, 'dispose_request_detail_id');
    }

}
