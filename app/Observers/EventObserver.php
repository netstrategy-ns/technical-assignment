<?php

namespace App\Observers;

use App\Models\Event;
use Illuminate\Support\Str;

class EventObserver
{
    public function saving(Event $event): void
    {
        if (empty($event->title) || $event->starts_at === null) {
            return;
        }

        if (! ($event->isDirty(['title', 'starts_at']) || empty($event->slug))) {
            return;
        }

        $baseSlug = Str::slug($event->title) . '-' . $event->starts_at->format('Y-m-d');
        $slug = $baseSlug;
        $n = 1;

        while (true) {
            $query = Event::query()->where('slug', $slug);

            if ($event->exists) {
                $query->where('id', '!=', $event->id);
            }

            if (! $query->exists()) {
                break;
            }

            $slug = $baseSlug . '-' . (++$n);
        }

        $event->slug = $slug;
    }
}
