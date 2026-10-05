<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseFileDataApplication extends Model
{
    use HasFactory;

    protected $table = 'case_file_data_application';

    protected $fillable = [
        'data_application_id',
        'case_file_id',
    ];
}
