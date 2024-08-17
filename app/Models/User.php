<?php

namespace App\Models;

use App\Http\Responses\BaseResponse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        'user_group_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'mobile_no',
        'address',
        'image',
        'fcm_token',
        'device_id',
        'device_type',
        'is_verify',
        'is_active',
        'is_super_admin',
        'is_notification'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function userSignup($params): mixed
    {
        $user = self::create([
            'first_name' => $params->first_name,
            'last_name' => $params->last_name,
            'email' => $params->email,
            'password' =>  Hash::make($params->password),
            'mobile_no' => $params->mobile_no,
            'address'  => $params->address,
            'fcm_token' => $params->fcm_token,
            'device_id' => $params->device_id,
            'device_type' => $params->device_type,
        ]);

        return $user;
    }

    public static function getUserById($user_id)
    {
        $query = self::where('id', $user_id)->first();
        return $query;
    }

}
