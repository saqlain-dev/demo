<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function TemplateType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'email_template_type')->select(['id', 'name']);
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function approvalProcess(): BelongsTo
    {
        return $this->belongsTo(ApprovalProcessName::class, 'approval_process_id')->withDefault();
    }
}
