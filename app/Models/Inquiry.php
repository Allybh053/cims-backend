<?php

namespace App\Models;

use App\Enums\InquiryEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => InquiryEnum::class
    ];

    protected $fillable = [
        'user_id',
        'admin_id',
        'answer',
        'question',
        'title',
        'respond_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inquiry_responses()
    {
        return $this->hasMany(InquiryResponse::class);
    }
}
