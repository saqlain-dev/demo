<?php

namespace App\Models\Admin\Library;

use App\Models\TypeValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookVariant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function book_type(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'book_type','id')->select(['id','name']);
    }
}
