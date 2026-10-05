<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegratedSearch extends Model
{
    use HasFactory;

    protected $table = 'search_logs';

    protected $fillable = [
        'user_id',
        'request_body',
        'response_body',
        'agency_id',
        'results',
    ];

}
