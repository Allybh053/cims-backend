<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventsCollection;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:event calendar read', ['only' => ['index', 'show']]);
        $this->middleware('can:event calendar create', ['only' => ['create', 'store']]);
        $this->middleware('can:event calendar update', ['only' => ['edit', 'update']]);
        $this->middleware('can:event calendar delete', ['only' => ['destroy']]);
    }

    public function index(): JsonResponse
    {
        try {
            $event = Event::get()->where('user_id', auth()->user()->id);

            return response()->json([
                'events' => new EventsCollection($event),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $event = Event::create([
                'user_id' => auth()->user()->id,
                'title' => $request->title,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'description' => $request->description,
            ]);
            return response()->json([
                'message' => 'Event created sucessfully',
                'event' => $event,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $event = Event::findOrFail($request->id);
            $event->update([
                'title' => $request->title,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'description' => $request->description,
            ]);
            return response()->json([
                'message' => 'Event updated sucessfully',
                'event' => $event,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);
            $event->delete();
            return response()->json([
                'message' => 'Event deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function filter(Request $request): JsonResponse
    {
        try {
            $events = array();
            if ($request->date) {
                $date = explode(' ', $request->date);

                $events = Event::where('user_id', auth()->user()->id)->where('start_time', 'like', "%{$date[0]}%")->get();
            } else {
                $events = Event::where('user_id', auth()->user()->id)->get();
            }

            return response()->json([
                'events' => new EventsCollection($events),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
