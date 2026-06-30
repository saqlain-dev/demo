<?php

namespace App\Models\Program\Rdu;

use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RmpMethodologyDetail extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function rMResponsibleNote(): BelongsTo
    {
        return $this->belongsTo(User::class, 'research_methodology_note_responsible')->select(['id','name']);
    }
}
