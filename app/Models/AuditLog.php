<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit;

class AuditLog extends Audit
{
    // Any customizations you want to add to the model
    protected $casts = [
        'created_at' => 'date:d M Y',
    ];
}



