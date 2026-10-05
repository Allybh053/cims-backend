<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase, HasMedia
{
    use HasDatabase, HasDomains, HasFactory, softDeletes, InteractsWithMedia;

     protected $fillable = [
         'id',
         'agency_name',
         'data',
         'status',
         'deleted_at'
     ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'agency_name',
            'status',
            'deleted_at'
        ];
    }

    public function tenantInfo(): HasOne
    {
        return $this->hasOne(TenantInfo::class);
    }

    public function users(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
