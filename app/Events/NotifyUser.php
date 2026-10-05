<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotifyUser
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;
    public $listenId;

    /**
     * Create a new event instance.
     */
    public function __construct(Notification $notification, Array $listenId)
    {
        // $this->user = $user;
        $this->notification = $notification;
        $this->listenId = $listenId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        // return [
        //     new PrivateChannel('channel-name'),
        // ];

        $arrayRecipients = [];

        foreach ($this->listenId as $key => $userId) {
            array_push($arrayRecipients, 'notify-' .$userId);
        }

        return $arrayRecipients;
    }

    public function broadcastAs()
    {
        return 'notification';
    }
}
