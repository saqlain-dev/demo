<?php

namespace App\Traits;

use App\Models\LogQuery;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

trait LogEvents
{
	public static function bootLogEvents(): void
	{
//        static::addGlobalScope('orderByIdDesc', function ($query) {
//            $query->orderBy('id', 'desc');
//        });

		static::creating(function (Model $model) {
			if ($model->getConnection()
				->getSchemaBuilder()
				->hasColumn($model->getTable(), 'created_by')) {
				$model->created_by = Auth::id();
			}
		});

		static::updating(function (Model $model) {
			if ($model->getConnection()
				->getSchemaBuilder()
				->hasColumn($model->getTable(), 'updated_by')) {
				$model->updated_by = Auth::id();
			}
		});

		static::deleting(function (Model $model) {
			if ($model->getConnection()
				->getSchemaBuilder()
				->hasColumn($model->getTable(), 'updated_by')) {
				$model->updated_by = Auth::id();
				$model->save();
			}
		});

		static::created(function (Model $model) {
			static::saveData($model, 'created');
		});
		static::updated(function (Model $model) {
			static::saveData($model, 'updated');
		});
		/*static::saving(function (Model $model) {
			static::saveData($model, 3);
		});*/
		static::deleted(function (Model $model) {
			static::saveData($model, 'deleted');
		});
		if (in_array(SoftDeletes::class, class_uses_recursive(get_called_class()))) {
			static::restored(function (Model $model) {
				static::saveData($model, 'restored');
			});
		}
	}

	static function saveData(Model $model, $type): void
	{
		if (auth()->check()) {
			$log = new LogQuery();
			$log->user_id = auth()->id();
			$log->table_name = $model->getTable();
			$log->query_type = $type;
			$log->data = $model->toJson();
			$log->save();
		}
	}
}
