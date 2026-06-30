<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class UserActivity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'email', 'ip_address', 'user_agent', 'event', 'created_at'
    ];
    // use it like this . UserActivity::log('view_dashboard'); 
    public static function log(string $event, $user = null)
    {
        $user = $user ?? auth()->user();

        if ($user) {
            \App\Models\UserActivity::create([
                'user_id'    => $user->id,
                'email'      => $user->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'event'      => $event,
                'created_at' => now(),
            ]);
        }
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,);
    }

}
