<?php

namespace App\Models\Admin;

use App\Models\User;
use App\Models\Admin\ItemVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemVariantLog extends Model
{
    protected $table = 'item_variant_logs';  
    protected $fillable = [
        'item_variant_id',
        'serial_no',
        'item_id',
        'inventory_id',
        'location_id',
        'store_id',
        'purchase_date',
        'assign_to_emp',
        'assign_to_dept',
        'inventory_type',
        'action',
        'changes',
        'created_by',
    ];

    protected $casts = [
        'changes' => 'array',
        'purchase_date' => 'date',
    ];

    public function itemVariant()
    {
        return $this->belongsTo(ItemVariant::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getChangesAttribute($value)
    {
        $data = json_decode($value, true);

        $relations = [
            'assign_to_emp' => \App\Models\Employee::class,
            'assign_to_dept' => \App\Models\TypeValue::class,
            'updated_by' => \App\Models\User::class,
        ];

        foreach (['old', 'new'] as $type) {
            if (!isset($data[$type])) continue;

            foreach ($relations as $key => $model) {
                if (isset($data[$type][$key])) {
                    $data[$type][$key] = $model::find($data[$type][$key]);
                }
            }
        }

        return $data;
    }

}
