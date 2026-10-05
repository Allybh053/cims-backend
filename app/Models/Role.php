<?php
namespace App\Models;
use Spatie\Permission\Models\Role as OriginalRole;
class Role extends OriginalRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'updated_at',
        'created_at',
    ];

    public function scopeFetchRoles($query)
    {
        if (auth()->user()->hasRole('super admin')) {
            return $query->whereIn('name', ['system admin', 'super admin'])->get();
        } else if (auth()->user()->hasRole('system admin')) {
            return $query->whereIn('name', ['agency admin'])->get();
        } else if (auth()->user()->hasRole('agency admin')) {
            return $query->whereNotIn('name', ['super admin', 'system admin', 'staff', 'agency admin'])->get();
        } else if (auth()->user()->hasRole('report admin')) {
            return $query->whereNotIn('name', ['super admin', 'system admin', 'report admin', 'staff'])->get();
        } else {
            return [];
        }
    }
}
