<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TenderLog extends Model
{
    protected $table = 'tender_logs';

    protected $fillable = [
        'tender_id',
        'name',
        'nature_id',
        'documents_ids',
        'opening_date',
        'closing_date',
        'is_comp_generated',
        'approval_status',
        'purchase_request_id',
        'expiry_date',
        'action',
        'changes',
        'created_by',
    ];

    protected $casts = [
        'changes' => 'array',
        'opening_date' => 'date',
        'closing_date' => 'datetime',
        'expiry_date' => 'date',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
