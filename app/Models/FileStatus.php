<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['status_name'];

    public function caseFiles()
    {
        return $this->hasMany(CaseFile::class);
    }
}
