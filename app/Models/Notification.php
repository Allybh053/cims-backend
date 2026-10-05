<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'data',
        'target_url',
        'notifiable_id',
        'notifiable_type',
        'read_at',
    ];

    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class)
            ->withPivot('read_at');
    }
}
