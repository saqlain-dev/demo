<?php

namespace App\Models\Admin\Library;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookRequest extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function BookId(): BelongsTo
    {
        return $this->belongsTo(Book::class,'book_id','id');
    }
    public function EmployeeId(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
