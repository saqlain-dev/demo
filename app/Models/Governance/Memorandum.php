<?php

namespace App\Models\Governance;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use mysql_xdevapi\Table;

class Memorandum extends Model
{
    protected $table = 'memorandums';
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];
}
