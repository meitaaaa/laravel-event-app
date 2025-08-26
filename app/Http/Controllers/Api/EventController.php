<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index(Request $r) // publik katalog
    {
        $q = Event::query()->where('is_published', true);

        if ($s = $r->get('q')) {
            $q->where(fn($w) =>
                $w->where('title', 'like', "%$s%")
                  ->orWhere('description', 'like', "%$s%")
                  ->orWhere('location', 'like', "%$s%")
            );
        }

        // sort: default terdekat
        $sort = $r->get('sort', 'soonest'); // soonest|latest|date
        if ($sort === 'soonest') {
            $q->orderBy('event_date')->orderBy('start_time');
        } elseif ($sort === 'latest') {
            $q->orderByDesc('event_date')->orderByDesc('start_time');
        }

        return $q->paginate(12);
    }

    public function store(StoreEventRequest $r)
    {
        // H-3 enforced via rules; double check juga
        if (now()->diffInDays(Carbon::parse($r->event_date), false) < 3) {
            return response()->json(['message' => 'Event must be created at least H-3.'], 422);
        }

        $data = $r->validated();
        if ($r->hasFile('flyer')) {
            $data['flyer_path'] = $r->file('flyer')->store('flyers', 'public');
        }
        if ($r->hasFile('certificate_template')) {
            $data['certificate_template_path'] = $r->file('certificate_template')->store('cert_templates', 'public');
        }
        $data['created_by'] = $r->user()->id;
        $data['registration_closes_at'] = Carbon::parse($r->event_date.' '.$r->start_time);

        $event = Event::create($data);
        return response()->json($event, 201);
    }

    public function show(Event $event)
    {
        return $event;
    }

    public function update(UpdateEventRequest $r, Event $event)
    {
        $data = $r->validated();
        if ($r->hasFile('flyer')) {
            $data['flyer_path'] = $r->file('flyer')->store('flyers', 'public');
        }
        if ($r->hasFile('certificate_template')) {
            $data['certificate_template_path'] = $r->file('certificate_template')->store('cert_templates', 'public');
        }
        $event->update($data);
        return $event;
    }

    public function publish(Request $r, Event $event)
    {
        $event->update(['is_published' => (bool) $r->boolean('is_published')]);
        return response()->json(['is_published' => $event->is_published]);
    }
}
