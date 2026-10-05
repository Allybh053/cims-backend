<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageCollection;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $messages = Message::all();
            return response()->json([
                'messages' => new MessageCollection($messages),
                'success' => 'OK'
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
            $chat = Chat::findOrFail($request->id);

            $broadcastTo = auth()->user()->id == $chat->sender_id ? $chat->recepient_id : $chat->sender_id;

            $message = Message::create([
                'sender_id' => auth()->user()->id,
                'chat_id' => $request->id,
                'msg' => $request->msg,
                'type' => $request->type,
                // 'attachments' => $request->attachments,
            ]);

            event(new MessageSent($broadcastTo, $message, $chat));

            return response()->json([
                'message' => 'Message created sucessfully',
                'message' => $message,
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
            $message = Message::findOrFail($request->id);
            $message->update([
                'title' => $request->title,
                'started_at' => date_format(date_create($request->started_at), 'Y-m-d'),
            ]);
            return response()->json([
                'message' => 'Message updated sucessfully',
                'message' => $message,
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
            $message = Message::findOrFail($id);
            $message->delete();
            return response()->json([
                'message' => 'Message deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

}
