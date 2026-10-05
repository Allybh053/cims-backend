<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = [
        'title',
        'slug',
        'icon',
        'route_name',
        'layout',
        'lvl',
        'position',
        'roles_id',
        'parent_id',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'roles_id');
    }
}
