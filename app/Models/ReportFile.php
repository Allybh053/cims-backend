<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ReportFile extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'visibility',
        'status',
    ];

public function scopeFetchReports($query)
{
    $user = auth()->user();

    if ($user->hasRole('report admin')) {
        // Admin can see all reports, with optional search functionality
        return $query->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                return $query->where('title', 'like', "%{$searchValue}%");
            })
            ->orderBy('created_at', 'desc');
    } else {
        $userRoles = array_diff($user->getRoleNames()->toArray(), ['staff']);

        if (!empty($userRoles)) {
            return $query->where('status', true)
                ->where(function ($query) use ($userRoles) {
                    foreach ($userRoles as $role) {
                        $query->orWhere('visibility', 'like', "%{$role}%")
                            ->orWhere('visibility', 'like', "%".ucfirst($role)."%");
                    }
                })
                ->orderBy('created_at', 'desc');
        } else {
            // Return an empty query if no roles are available
            return $query->whereRaw('1 = 0');
        }
    }
}

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
