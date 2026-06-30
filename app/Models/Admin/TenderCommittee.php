<?php

namespace App\Models\Admin;

use App\Models\User;
use App\Models\Vendor;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderCommittee extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    public function userDetail(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'tender_committee_vendor');
    }
}
