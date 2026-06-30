<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogQuery extends Model
{
	protected $fillable = [
		'user_id',
		'table_name',
		'query_type',
		'data',
	];
}
