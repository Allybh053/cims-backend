<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements HasMedia, Auditable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use SoftDeletes;
    use InteractsWithMedia;
    use \OwenIt\Auditing\Auditable;

    // protected $connection = 'tenant';
    // $model->setConnection('tenant');

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'profile_pic_path',
        'active_status',
        'uuid',
        'email',
        'password',
        'agency_id',
        'agency_name',
        'fido_login_at',
        'fido_registered_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'profile_image',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // public function agency()
    // {
    //     return $this->belongsTo(Agency::class);
    // }

    public function getProfileImageAttribute(): ?string
    {
        $media = $this->getFirstMedia('profile_image');

        if ($media) {
            return $media->getFullUrl();
        }

        return null;
    }

    public function scopeIsSuperAdmin($query)
    {
        return $query->hasRole('super admin');
    }

    public function scopeIsSystemAdmin($query)
    {
        return $query->hasRole('system admin');
    }

    public function scopeIsStaff($query)
    {
        return $query->hasRole('staff');
    }

    public function scopeHasAccess($query, $id)
    {
        return $query->whereHas('caseFiles', function ($query) use ($id) {
            $query->where('case_file_id', $id);
        })->get();
    }

    public function scopeSharedList($query, $id)
    {
        return $query->whereDoesntHave('caseFiles', function ($query) use ($id) {
            $query->where('case_file_id', $id);
        })->where('active_status', true)->get();
    }

    public function scopeUserList($query, $search)
    {
        $user = auth()->user();

        // Base search query
        $query->when($search, function ($queryWhen, $searchValue) {
            $queryWhen->where(function ($querySearch) use ($searchValue) {
                $querySearch->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('email', 'like', "%{$searchValue}%")
                    ->orWhere('agency_name', 'like', "%{$searchValue}%");
            });
        });

        // Role-specific conditions
        if ($user->hasRole('agency admin')) {
            $query->role('staff')
                  ->where('agency_id', $user->agency_id)
                  ->whereHas('roles', function($query) {
                      $query->whereIn('name', ['investigator', 'report admin', 'data request admin']);
                  });
        } elseif ($user->hasRole('system admin')) {
            $query->whereHas('roles', function($query) {
                      $query->whereNotIn('name', ['super admin', 'system admin']);
                  });
        } elseif ($user->hasRole('super admin')) {
            // No additional filters for super admin other than the search filters
        } else {
            // Handle other roles or return an empty result
            return $query->whereRaw('1 = 0'); // Ensures no results are returned for other roles
        }

        // Order and paginate the results
        return $query->orderBy('created_at', 'desc')->paginate(10);
    }


    public function scopeDeletedUserList($query, $search)
    {
        if (auth()->user()->hasRole('super admin')) {
            return $query
                ->onlyTrashed()
                ->when($search, function ($queryWhen, $searchValue) {
                    return $queryWhen->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('email', 'like', "%{$searchValue}%")
                        ->orWhere('agency_name', 'like', "%{$searchValue}%");
                })
                ->paginate(10);

        } else if (auth()->user()->hasRole('system admin')) {
            return $query
                ->onlyTrashed()
                ->whereHas('roles', function($query) {
                    $query->whereNotIn('name', ['super admin', 'system admin']);
                })
                ->when($search, function ($queryWhen, $searchValue) {
                    return $queryWhen->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('email', 'like', "%{$searchValue}%")
                        ->orWhere('agency_name', 'like', "%{$searchValue}%");
                })
                ->paginate(10);
        }

        return [];
    }

    public function forums(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'agency_id');
    }

    public function caseFiles(): BelongsToMany
    {
        return $this->belongsToMany(CaseFile::class)
            ->withPivot('status', 'expired_at')
            ->withTimestamps();
    }

    public function notifications(): BelongsToMany {
        return $this->belongsToMany(Notification::class)
            ->withPivot('read_at');
    }

    public function announcements(): BelongsToMany {
        return $this->belongsToMany(Announcement::class)
            ->withPivot('read_at');
    }
}
