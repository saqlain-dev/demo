<?php

namespace App\Models\Communication;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventCategory extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function subCategories(): HasMany
    {
        return $this->hasMany(EventSubCategory::class, 'category_id');
    }

    public function eventDetails(): HasMany
    {
        return $this->hasMany(CommunicationEventDetail::class, 'category_id');
    }


}
