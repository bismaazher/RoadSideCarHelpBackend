<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'user_id', 'is_expired','type'
    ];

    public static function getCode($params)
    {
        $code = self::where(['user_id' => $params->id, 'code' => $params->code,'is_expired' => 0 ])->first();
        return $code;
    }

   

}
