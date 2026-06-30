<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeEobi extends Model
{
    use HasFactory, LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function created_by(): BelongsTo
    {
        return $this->BelongsTo(User::class,'created_by')->select(['id','name']);
    }
}
