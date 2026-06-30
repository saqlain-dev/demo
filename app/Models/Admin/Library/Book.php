<?php

namespace App\Models\Admin\Library;

use App\Models\Admin\Location;
use App\Models\User;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Library\BookVariant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    protected $table = 'books';

    public function booksIssued()
    {
        return $this->hasMany(BookIssued::class, 'book_id');
    }

    public function Reconciliation(): HasMany
    {
        return $this->hasMany(BookReconciliationDetail::class,'book_id');
    }
    public function book_type(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'book_type','id')->select(['id','name']);
    }
    public function BookCategory(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'book_category','id')->select(['id','name']);
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function bookVariant(): HasMany
    {
        return $this->hasMany(BookVariant::class, 'book_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class,'location');
    }
}
