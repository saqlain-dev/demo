<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RfqWaiver extends Model
{
    use HasFactory, LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function approvers()
    {
        return $this->belongsToMany(User::class, 'rfq_waiver_user')
                    ->withPivot('is_approved', 'message')
                    ->withTimestamps();
    }

}
