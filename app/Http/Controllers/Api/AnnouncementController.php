<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementsCollection;
use App\Models\Announcement;
use App\Models\AnnouncementUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $role = auth()->user()->roles->first()->name;

            $userAnnouncements = User::with('announcements')->whereId(auth()->user()->id)->first();

            $announcements = [];

            if($userAnnouncements){
                $announcements = $userAnnouncements->announcements->filter(function($item) use ($role) {
                    if (Carbon::now()->between($item->date_start, $item->date_end) && in_array($role, array_map('strtolower', json_decode($item->visibility)))) {
                        return $item;
                    }
                });
            }

            //Original code
            // $announcements = Announcement::all()->filter(function($item) use ($role) {
            //     if (Carbon::now()->between($item->date_start, $item->date_end) && in_array($role, array_map('strtolower', json_decode($item->visibility)))) {
            //         return $item;
            //     }
            // });

            return response()->json([
                'announcements' => new AnnouncementsCollection($announcements),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Update announcement
            $announcementUser = AnnouncementUser::where('announcement_id', $id)->where('user_id', auth()->user()->id)->first();
            if(!$announcementUser->read_at){
                $announcementUser->read_at = now();
            }else{
                $announcementUser->read_at = null;
            }
            $announcementUser->save();

            return response()->json([
                'message' => 'Announcement updated successfully',
                'announcement' => $announcementUser,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
