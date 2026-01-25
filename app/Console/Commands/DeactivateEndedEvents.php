<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class DeactivateEndedEvents extends Command
{
    protected $signature = 'events:deactivate-ended';

    protected $description = 'Deactivate events that have ended.';

    public function handle(): int
    {
        $today = now()->toDateString();

        $activated = Event::query()
            ->whereNotNull('starts_at')
            ->whereNotNull('ends_at')
            ->whereDate('starts_at', '<=', $today)
            ->whereDate('ends_at', '>=', $today)
            ->update(['is_active' => true]);

        $deactivated = Event::query()
            ->whereNotNull('starts_at')
            ->whereNotNull('ends_at')
            ->where(function ($query) use ($today): void {
                $query
                    ->whereDate('ends_at', '<', $today)
                    ->orWhereDate('starts_at', '>', $today);
            })
            ->update(['is_active' => false]);

        $this->info("Activated {$activated} event(s).");
        $this->info("Deactivated {$deactivated} event(s).");

        return self::SUCCESS;
    }
}
