<?php

namespace App\Models\Questionnaire;

use App\Enum\FormCategory;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Laravel\Prompts\select;

class QuestionnaireForm extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    //protected $with = ['created_by','updated_by'];


    public function questions(): HasMany
    {
        return $this->hasMany(Question::class,'form_id');
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by')->select(['id','name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by')->select(['id','name']);
    }

    public static function getMneTools(): Collection
    {
        return self::where('form_category',FormCategory::MnePlan->value)->get();
    }

    public function questionnaires(): HasMany
    {
        return $this->hasMany(Questionnaire::class, 'form_id');
    }


}
