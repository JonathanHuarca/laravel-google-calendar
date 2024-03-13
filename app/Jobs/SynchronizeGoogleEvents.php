<?php

namespace App\Jobs;

use App\Jobs\SynchronizeGoogleResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Services\Google;

class SynchronizeGoogleEvents extends SynchronizeGoogleResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function getGoogleService()
    {
        return app(Google::class)
            ->connectWithSynchronizable($this->synchronizable)
            ->service('Calendar');
    }

    public function getGoogleRequest($service, $options)
    {
        return $service->events->listEvents(
            $this->synchronizable->google_id, $options
        );
    }

    public function syncItem($googleEvent)
    {
        // Aqui se elimina un evento especifico
        if ($googleEvent->status === 'cancelled') {
            return $this->synchronizable->events()
                ->where('google_id', $googleEvent->id)
                ->delete();
        }

        $this->synchronizable->events()->updateOrCreate(
            [
                'google_id' => $googleEvent->id,
            ],
            [
                'name' => $googleEvent->summary ?? '(No title)',
                'description' => $googleEvent->description,
                'allday' => $this->isAllDayEvent($googleEvent),
                'started_at' => $this->parseDatetime($googleEvent->start),
                'ended_at' => $this->parseDatetime($googleEvent->end),
            ]
        );
    }

    public function dropAllSyncedItems()
    {
        $this->synchronizable->events()->delete();
    }

    protected function isAllDayEvent($googleEvent)
    {
        return ! $googleEvent->start->dateTime && ! $googleEvent->end->dateTime;
    }

    protected function parseDatetime($googleDatetime)
    {
        $rawDatetime = $googleDatetime->dateTime ?: $googleDatetime->date;

        return Carbon::parse($rawDatetime)->setTimezone('UTC');
    }
}
