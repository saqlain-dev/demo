<?php

namespace App\Models\Finance\Voucher;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralLedger extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];
    protected $table = 'tbl_general_ledgers';

    public function ledgerDetail(): HasMany
    {
        return $this->hasMany(GeneralLedgerDetail::class,'Gl_Id', 'id');
    }
}
