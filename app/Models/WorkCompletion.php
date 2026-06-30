<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCompletion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'vender_id',
        'invoice_date',
        'branch_office_id',
    ];

    protected $dates = [
        'invoice_date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vender_id');
    }

    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branch_office_id');
    }
}