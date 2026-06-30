<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type extends Model
{
    use SoftDeletes;
	protected $guarded = ['id'];

	public function values(): HasMany
	{
		return $this->hasMany(TypeValue::class);
	}

	public static function getTypeValues($type_key)
	{
		$type = Type::query()->where('key', $type_key)->first();
		return $type->values ?? [];
	}
    public static function getTypeValuesWithTranshed($type_key)
    {
        $type = Type::query()->where('key', $type_key)->first();
        if (!$type) {
            return [];
        }

        return $type->values()->withTrashed()->get();
    }


    public static function getDisaggregates()
    {
        $type = self::with('values')->where('is_disaggregate', 1)->get();
        return $type ?? [];
    }
}
