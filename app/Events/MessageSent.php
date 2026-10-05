<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Message;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    // public $user;
    public $chatId;
    public $message;
    public $chat;

    /**
     * Create a new event instance.
     */
    public function __construct(String $chatId, Message $message, Chat  $chat)
    {
        // $this->user = $user;
        $this->chatId = $chatId;
        $this->message = $message;
        $this->chat = $chat;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        // return new PrivateChannel('chat-' .$this->chatId);
        // return ['chat-' .$this->message->sender_id];
        return ['chat-' .$this->chat->recepient_id, 'chat-' .$this->chat->sender_id];
        // return 'chat-' . $this->chatId;
    }

    public function broadcastAs()
    {
        return 'message';
    }
}
