<?php

namespace App\Traits;

use App\Models\AcknowledgementHistory;

trait LogsAcknowledgementDate
{
    protected static function bootLogsAcknowledgementDate()
    {
        static::updating(function ($model) {
            if ($model->isDirty('last_acknowledgement_date')) {
                AcknowledgementHistory::create([
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'old_acknowledgement_date' => $model->getOriginal('last_acknowledgement_date'),
                    'new_acknowledgement_date' => $model->last_acknowledgement_date,
                    'updated_by' => auth()->id(),
                ]);
            }
        });
    }

    public function model()
    {
        return $this->morphTo();
    }
}
