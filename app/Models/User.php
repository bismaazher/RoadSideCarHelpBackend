<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

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
        'birthday',
        'gender',
        'image',
        'fcm_token',
        'stripe_token',
        'device_id',
        'device_type',
        'is_verify',
        'is_active',
        'google_id',
        'facebook_id',
        'apple_id',
        'type',
        'age',
        'month',
        'day',
        'year',
        'meta_data',
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

    public function coahExpertise()
    {
        return $this->hasMany(CoachExpertise::class,'user_id','id');
    }

    public function coachAvailableSlot()
    {
        return $this->hasMany(CoachAvailableSlot::class,'user_id','id');
    }

    public function coachDetails()
    {
        return $this->hasOne(CoachDetail::class,'user_id','id');
    }

    public static function userSignup($params): mixed
    {
        $user = self::create([
            'first_name' => $params->first_name,
            'last_name' => $params->last_name,
            'email' => $params->email,
            'password' =>  Hash::make($params->password),
            'mobile_no' => $params->mobile_no,
            'fcm_token' => $params->fcm_token,
            'device_id' => $params->device_id,
            'device_type' => $params->device_type,
        ]);

        return $user;
    }

    public static function getUserById($user_id)
    {
        $query = self::with(['coachDetails','coahExpertise', 'coachAvailableSlot'])
                        ->where('id', $user_id)->first();
        return $query;
    }

    public static function getCoachList()
    {
        $coach = self::where('user_group_id', '2')->get();
        return $coach;
    }

    public static function getMyStudent($coach_id)
    {
        return true;
    }

   

    public static function socialUser($data, $type)
    {
        $names = explode(' ', Arr::get($data, 'name', ''));
        $nickname = explode(' ', Arr::get($data, 'nickname', ''));

        $userData = [
            'user_group_id' => Arr::get($data, 'user_group_id', ''),
            'first_name' => Arr::get($names, '0', ''),
            'last_name' => Arr::get($nickname, '0', ''),
            'email' => Arr::get($data, 'email', ''),
            'mobile_no' => '',
            'image' => Arr::get($data, 'avatar', ''),
            'fcm_token' => Arr::get($data, 'fcm_token', ''),
            'device_id' => Arr::get($data, 'device_id', ''),
            'device_type' => Arr::get($data, 'device_type', ''),
            'is_verify' => 1,
            'is_active' => 1,
            'created_at' => now(),
        ];

        if ($type == 'google') {
            $userData['google_id'] = Arr::get($data, 'id', '');
        }
        if ($type == 'facebook') {
            $userData['facebook_id'] = Arr::get($data, 'id', '');
        }
        if ($type == 'apple') {
            $userData['apple_id'] = Arr::get($data, 'id', '');
        }

        $user = User::create($userData);
        return $user;
    }

    public static function getHabitList()
    {
        $list = \DB::table('habits')->get();
        return $list;
    }

    public static function getActivityList()
    {
        $list = \DB::table('activity')->get();
        return $list;
    }

    public static function getFieldList()
    {
        $list = \DB::table('fields')->get();
        return $list;
    }

}
