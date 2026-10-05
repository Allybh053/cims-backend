<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends BaseMedia
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
}
