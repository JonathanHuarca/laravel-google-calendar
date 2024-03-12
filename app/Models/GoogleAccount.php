<?php

namespace App\Models;


use App\Concerns\Synchronizable;
use App\Jobs\SynchronizeGoogleCalendars;
use App\Jobs\WatchGoogleCalendars;
use App\Services\Google;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Calendar;

class GoogleAccount extends Model
{
    use Synchronizable;

    protected $fillable = ['user_id', 'google_id', 'name', 'token'];
    protected $casts = ['token' => 'json'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }

    public function getToken()
    {
        return $this->token;
    }

    public function synchronize()
    {
        SynchronizeGoogleCalendars::dispatch($this);
    }

    public function watch()
    {
        WatchGoogleCalendars::dispatch($this);
    }
}
