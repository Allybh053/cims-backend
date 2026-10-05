<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'sender_name',
        'sender_agency_name',
        'recepient_id',
        'recepient_name',
        'recepient_agency_name',
        'recent'
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recepient_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
