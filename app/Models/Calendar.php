<?php

namespace App\Models;

use App\Concerns\Synchronizable;
use App\Jobs\SynchronizeGoogleEvents;
use App\Jobs\WatchGoogleEvents;
use Illuminate\Database\Eloquent\Model;
use App\Models\GoogleAccount;
use App\Models\Event;

class Calendar extends Model
{
    use Synchronizable;

    protected $fillable = ['google_account_id', 'google_id', 'name', 'color', 'timezone'];

    public function googleAccount()
    {
        return $this->belongsTo(GoogleAccount::class, 'google_account_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function getToken()
    {
        return $this->googleAccount->token;
    }

    public function synchronize()
    {
        SynchronizeGoogleEvents::dispatch($this);
    }

    public function watch()
    {
        WatchGoogleEvents::dispatch($this);
    }
}
