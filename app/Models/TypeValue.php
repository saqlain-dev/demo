<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeValue extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id','id');
    }

    public function dependent(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'parent_id','id');
    }

    public static function restoreValue($id)
    {
        $value = self::onlyTrashed()->find($id);

        if ($value) {
            $value->restore();
            return $value;
        }

        return null;
    }
    

}
