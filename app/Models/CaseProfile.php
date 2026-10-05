<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['case_id', 'data', 'is_create_from_user'];
}
