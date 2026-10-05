<?php

namespace App\Http\Controllers\Api;

use App\Events\NotifyUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationCollection;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{

    public function index(): JsonResponse
    {
        try {
            // $notification = Notification::with('messages')->get();
            // $notification = Notification::where('user_id', auth()->user()->id)->get();
            $notificationUser = User::with('notifications')->where('id', auth()->user()->id)->first();

            $notifications = [];

            if($notificationUser){
               $notifications = $notificationUser->notifications;
            }

            return response()->json([
                // 'notifications' => $notification->toArray(),
                'notifications' => new NotificationCollection($notifications),
                // 'success' => 'OK'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function indexPaginated(): JsonResponse
    {
        try {
            // $notification = Notification::with('messages')->get();
            // $notification = Notification::where('user_id', auth()->user()->id)->get();
            $notificationUser = User::with('notifications')->where('id', auth()->user()->id)->first();

            $notifications = [];

            if($notificationUser){
               $notifications = $notificationUser->notifications()->paginate(10);
            }

            return response()->json([
                'notifications' => new NotificationCollection($notifications),
                'pagination' => new PaginationResource($notifications)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

   public function notificationUpdate(Request $request, $id)
    {
        try {
            // Update announcement
            $notificationUser = NotificationUser::where('notification_id', $id)->where('user_id', auth()->user()->id)->first();

            if(!$notificationUser->read_at){
                $notificationUser->read_at = now();
            }else{
                $notificationUser->read_at = null;
            }
            $notificationUser->save();

            return response()->json([
                'message' => 'Notification updated successfully',
                'announcement' => $notificationUser,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }


    public function store(Request $request): JsonResponse
    {
        try {
                $notification = Notification::create([
                    'type' => 'general',
                    'sender_name' => auth()->user()->name,
                    'data' => '',
                    'target_url' => '',
                ]);

                event(new NotifyUser($notification, auth()->user()->agency_id));

                return response()->json([
                    'message' => 'Notification created successfully',
                    'notifications' => $notification,
                ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();
            return response()->json([
                'message' => 'Notification deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

}
