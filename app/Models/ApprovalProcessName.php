<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalProcessName extends Model
{
    protected $guarded=['id'];
    public function approvalProcess(): HasMany
    {
        return $this->hasMany(ApprovalProcess::class,'approval_process_id');
    }
}
