<?php

namespace App\Models\Admin\Fleet;

use App\Models\User;
use App\Models\Employee;
use App\Models\Admin\Fleet\FeedBack;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Fleet\VehicleRequest;
use App\Models\Admin\Fleet\FeedBackQuestion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FleetFeedBack extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function question():BelongsTo
    {
        return $this->belongsTo(FeedBackQuestion::class, 'question_id')->select('id','question');
    }

    public function requisition():BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class, 'requisition_id');
    }
    public function feedBack():BelongsTo
    {
        return $this->belongsTo(FeedBack::class, 'feed_back_id');
    }
    public function employee():BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }


}
