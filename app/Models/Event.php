<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Calendar;


class Event extends Model
{
    protected $with = ['calendar'];
    protected $fillable = ['calendar_id', 'google_id', 'name', 'description', 'allday', 'started_at', 'ended_at'];

    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'calendar_id');
    }

    public function getStartedAtAttribute($start)
    {
        return $this->asDateTime($start)->setTimezone($this->calendar->timezone);
    }

    public function getEndedAtAttribute($end)
    {
        return $this->asDateTime($end)->setTimezone($this->calendar->timezone);
    }

    public function getDurationAttribute()
    {
        return $this->started_at->diffForHumans($this->ended_at, true);
    }
}
