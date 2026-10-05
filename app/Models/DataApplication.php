<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DataApplication extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

//    protected $table = 'data_applications';

    protected $fillable = [
        'title',
        'case_file_id',
        'description',
        'requestor_agency_id',
        'requestor_id',
        'receiver_agency_id',
        'receiver_id',
        'request_body',
        'response_body',
        'poi_name',
        'poi_ic',
        'approver_id',
        'approver_date',
        'reject_reason',
        'reject_reason_description',
        'reject_remark',
        'references_no',
        'data_application_status_id',
    ];

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'requestor_agency_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'receiver_agency_id');
    }

    public function dataApplicationStatus(): BelongsTo
    {
        return $this->belongsTo(DataApplicationStatus::class);
    }

    public function requestorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }
}
