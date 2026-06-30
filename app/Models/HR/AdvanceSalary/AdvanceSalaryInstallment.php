<?php

namespace App\Models\HR\AdvanceSalary;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvanceSalaryInstallment extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function loanSettlement(): HasOne
    {
        return $this->hasOne(LoanSettlement::class,'ReferenceId');
    }
}
