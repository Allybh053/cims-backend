<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'user_id',
        'title',
        'status',
        'description',
        'date_start',
        'date_end',
        'visibility',
    ];

    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('read_at');
    }


}
