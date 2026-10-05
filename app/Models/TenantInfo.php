<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantInfo extends Model
{
    use HasFactory, softDeletes;

    protected $table = 'tenant_info';

    protected $fillable = ['tenant_id', 'address_1', 'address_2', 'address_3', 'postcode', 'city', 'state', 'country', 'company', 'phone_no', 'fax_no', 'website', 'email', 'rep_type_id', 'rep_id_no', 'rep_name', 'rep_designation', 'rep_dept_unit', 'rep_email', 'rep_phone_no', 'rep_mobile_no'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
