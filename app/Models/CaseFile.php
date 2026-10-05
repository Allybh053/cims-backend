<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CaseFile extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = ['case_ref_id', 'title', 'start_date', 'end_date', 'status_id', 'case_description', 'data', 'properties', 'original_name', 'level_id', 'case_category', 'last_closed_at'];

    public function fileStatus(): BelongsTo
    {
        return $this->belongsTo(FileStatus::class, 'status_id');
    }

    public function fileLevel(): BelongsTo
    {
        return $this->belongsTo(FileLevel::class, 'level_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(InvestigationNote::class, 'case_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status', 'expired_at')
            ->withTimestamps();
    }
}
