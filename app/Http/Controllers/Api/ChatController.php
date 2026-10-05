<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatCollection;
use App\Http\Resources\User\UsersCollection;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:chat read', ['only' => ['index']]);
        $this->middleware('can:chat create', ['only' => ['store']]);
        $this->middleware('can:chat update', ['only' => ['edit','update']]);
        $this->middleware('can:chat delete', ['only' => ['destroy']]);
    }

    public function index(): JsonResponse
    {
        try {
            // $chat = Chat::with('messages')->get();
            $chats = Chat::where('sender_id', auth()->user()->id)->orWhere('recepient_id', auth()->user()->id)->with('messages')->get();

            return response()->json([
                // 'chats' => $chat->toArray(),
                'chats' => ChatCollection::collection($chats),
                'success' => 'OK'
            ], 200);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function contacts(): JsonResponse
    {
        try {
            $existsChatUser = [];

            $existSender = Chat::where('sender_id', auth()->user()->id)
                ->pluck('recepient_id')->toArray();

            $existReceiver = Chat::where('recepient_id', auth()->user()->id)
                ->pluck('sender_id')->toArray();

            $existsChatUser = array_merge($existSender, $existReceiver);
            array_push($existsChatUser, auth()->user()->id);

            $users = User::whereNotIn('id',array_unique($existsChatUser))->whereAgencyId(auth()->user()->agency_id)->get();

            // do add branch and department here if needed

            return response()->json([
                'contacts' => new UsersCollection($users),
                'success' => 'OK'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function fullcontacts(): JsonResponse
    {
        try {

            $users = User::whereAgencyId(auth()->user()->agency_id)->get();

            return response()->json([
                'contacts' => new UsersCollection($users),
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
            $listOfUsers = User::whereIn('id', $request->chats)->get();
            $chats = array();

            foreach ($listOfUsers as $key => $user) {
                $chatExists = Chat::whereIn('sender_id', [$user->id, auth()->user()->id])
                    ->whereIn('recepient_id', [$user->id, auth()->user()->id])
                    ->first();

                if(!$chatExists){
                    $chat = Chat::create([
                        'sender_id' => auth()->user()->id,
                        'sender_name' => auth()->user()->name,
                        'sender_agency_name' => auth()->user()->agency_name,
                        'recepient_id' => $user->id,
                        'recepient_name' => $user->name,
                        'recepient_agency_name' => $user->agency_name,
                        'recent' => false,
                        // 'started_at' => date_format(date_create($request->started_at), 'Y-m-d'),
                    ]);

                    array_push($chats, $chat);
                }
            }

            if(count($chats) > 0){
                return response()->json([
                    'message' => 'Chat created successfully',
                    'chats' => $chats,
                ], 200);
            }

            return response()->json([
                'message' => 'Chat is not created',
            ], 500);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $chat = Chat::findOrFail($request->id);
            $chat->update([
                'sender_name' => $request->title,
            ]);
            return response()->json([
                'message' => 'Chat updated sucessfully',
                'chat' => $chat,
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
            $chat = Chat::findOrFail($id);
            $chat->delete();
            return response()->json([
                'message' => 'Chat deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

}
