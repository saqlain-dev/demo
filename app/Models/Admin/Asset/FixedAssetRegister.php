<?php

namespace App\Models\Admin\Asset;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAssetRegister extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];


    public function depreciations()
    {
        return $this->hasMany(FixedAssetDepreciation::class, 'register_id');
    }
}
