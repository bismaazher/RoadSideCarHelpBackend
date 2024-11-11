<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'notify_user_id',
        'notify_user_type',
        'other_user_id',
        'other_user_type',
        'title',
        'message',
        'data',
        'notification_type',
        'is_read'
    ];
}
