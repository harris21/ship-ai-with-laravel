<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Laravel\Ai\Events\AgentFailedOver;

class NotifyOnFailover
{
    public function handle(AgentFailedOver $event): void
    {
        Log::warning('Ai Provider Failover', [
            'from' => $event->failedProvider,
            'to' => $event->activeProvider,
            'agent' => get_class($event->agent),
        ]);
    }
}
