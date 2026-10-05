<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Event extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'reminder_time',
    ];

    protected $appends = [
        'started_day',
        'started_date',
        'duration',
    ];

    public function getStartedDayAttribute()
    {
        return date_format(date_create($this->start_time), 'D');
    }

    public function getStartedDateAttribute()
    {
        return date_format(date_create($this->start_time), 'd F Y');
    }

    public function getDurationAttribute()
    {
        $from = Carbon::parse($this->start_time);
        if ($this->end_time != NULL) {
            $to = Carbon::parse($this->end_time);
        } else {
            $to = Carbon::parse($this->start_time)->addDay(1);
        }
        return $from->diff($to)->days . ' days';
    }

    public function event()
    {
        return $this->belongsTo(User::class);
    }
}
