<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $connection = 'mysql';

    protected $fillable = [
        'user_id',
        'message',
        'url',
        'read'
    ];
}
