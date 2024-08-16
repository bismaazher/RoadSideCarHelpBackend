<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\SetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UserLoginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Responses\BaseResponse;
use App\Models\CoachDetail;
use App\Models\UserOtp;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthUserController extends Controller
{
    private $currentUser;

    function __construct()
    {   
        $this->currentUser = auth('api')->user();
    }

    public function register(UserRegisterRequest $request): mixed
    {
        //print_r("here");
        DB::beginTransaction();
        $user = User::userSignup($request);
        if ($user) {
            $token = auth('api')->login($user);
            if ($token) {
                $this->sendOTP($user);
                DB::commit();
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Kindly check your phone.", $user, $token);
            } else {
                return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "Failed to Sign up");
            }
        }
    }

    public function login(UserLoginRequest $request)
    {
        DB::beginTransaction();

        if (!$token = auth('api')->attempt($request->only(['email', 'password']))) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Incorrect email or password");
        }

        if ((!Auth::guard('api')->user()->is_verify)) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Please verify your phone number.");
        }

        if (auth('api')->check()) {
            $agent = auth('api')->user();
            $agent->fcm_token = $request->fcm_token;
            $agent->device_id = $request->device_id;
            $agent->device_type = $request->device_type;
            $agent->save();
        }

        if ($agent && $token) {
            $this->sendOTP($agent);
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Logged in successfully.", $agent, $token);
        }
    }

    function getUserProfile($id)
    {
        $record = User::getUserById($id);

        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Success", $record);
    }

    //student to get coach list and coach to get my student list
    function getUserList(Request $request)
    {
        $user = $this->currentUser;
        $params = $request->all();

        if( $user['user_group_id'] == '2' ){
            $record = User::getCoachList();
        }else{
            $record = User::getMyStudent($user['id']);
        }

        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Success", $record);
    }

    function updateProfile(UpdateProfileRequest $request)
    {
        if ($this->currentUser) {
            $data = $request->except(['image']);
            if ($request->file('image')) {
                $data['image'] = uploadImage($request->file('image'), 'user', $this->currentUser?->image ?? null);
            }
            $this->currentUser->update($data);
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Profile has been updated.", $this->currentUser);
        } else {
            return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "User unauthorized.");
        }
    }

    function userProfileDetails(Request $request)
    {
        $user_id = $this->currentUser->id;
        $params = $request->all();
        $record = CoachDetail::coachProfileDetails($user_id, $params);
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Coach Profile has been updated.", $record);
    }

    public function sendOTP(User $user)
    {
        $otp = rand(100000, 999999);
        UserOtp::where(['user_id' => $user->id, 'is_expired' => 0])->delete();

        UserOtp::create([
            'code' => $otp,
            'user_id' => $user->id,
        ])->code;

        //Mail::to($user->email)->send(new SendOtp($otp));
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        DB::beginTransaction();
        $user = auth('api')->user();

        $checkCode = UserOtp::getCode($request);

        if ($request->code == '123456') {
            $user->is_verify = 1;
            $user->save();
            $token = auth('api')->login($user);
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully verified", $user, $token);
        } else if ($checkCode && $request->code == $checkCode->code) {
            $user->is_verify = 1;
            $user->save();
            $token = auth('api')->login($user);
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully verified", $user, $token);
        } else {
            return new BaseResponse(STATUS_CODE_CREATE, STATUS_CODE_CREATE, "Incorrect code.");
        }
    }

    public function resendOtp()
    {
        DB::beginTransaction();
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $this->sendOTP($user);
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "OTP send successfully");
        }

        return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "User authorized.");
    }

    public function forgot(ForgotPasswordRequest $request)
    {
        DB::beginTransaction();
        $user = User::where('mobile_no', $request->mobile_no);
        if ($user->count()) {
            $user = $user->first();
            $token = auth('api')->fromUser($user);
            $this->sendOTP($user);
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully send OTP", "", $token);
        } else {
            DB::rollBack();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Customer does not exist!");
        }
    }

    public function changePassword(SetPasswordRequest $request)
    {
        DB::beginTransaction();
        if (!$request->is_forgot)
            if (!Hash::check($request->old_password, $this->currentUser->password))
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Incorrect Old Password!");

        $this->currentUser->password = Hash::make($request->password);
        $this->currentUser->save();
        DB::commit();

        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully set password");
    }

    function getSocialData(Request $request)
    {
        $request->validate([
            'type' => 'required|in:facebook,google,apple'
        ], [
            'type.in' => 'The selected type should be in google,facebook,apple'
        ]);

        if (str($request->type)->contains(['google', 'facebook', 'apple'])) {
            return $this->checkAlreadyUser($request->all());
        } else {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Something went wrong.");
        }
    }

    function checkAlreadyUser(array $data)
    {
        if (Arr::get($data, 'type') == 'google' && Arr::get($data, 'id')) {
            $user = User::where('google_id', Arr::get($data, 'id'))->orWhere('email', Arr::get($data, 'email'))->first();
            return $this->signInAsSocial($user, $data, 'google');
        } elseif (Arr::get($data, 'type') == 'facebook' && Arr::get($data, 'id')) {
            $user = User::where('facebook_id', Arr::get($data, 'id'))->orWhere('email', Arr::get($data, 'email'))->first();
            return $this->signInAsSocial($user, $data, 'facebook');
        } elseif (Arr::get($data, 'type') == 'apple' && Arr::get($data, 'id')) {
            $user = User::where('apple_id', Arr::get($data, 'id'))->orWhere('email', Arr::get($data, 'email'))->first();
            return $this->signInAsSocial($user, $data, 'apple');
        } else {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Something went wrong.");
        }
    }

    function signInAsSocial($user, $data, $type)
    {
        DB::beginTransaction();
        if ($user) {
            $token = auth('api')->login($user);
            if ($token) {
                DB::commit();
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Logged in successfully.", $user, $token);
            } else {
                return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "Failed to Sign up");
            }
        } else {
            $socialUser = User::socialUser($data, $type);

            if ($socialUser) {
                $token = auth('api')->login($socialUser);
                if ($token) {
                    DB::commit();
                    return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Logged in successfully.", $socialUser, $token);
                } else {
                    return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "Failed to Sign up");
                }
            }
        }
    }

    public function logout()
    {
        if (auth('api')->check()) {
            $this->currentUser->fcm_token = null;
            $this->currentUser->save();
            auth()->guard('api')->logout();

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully logout");
        } else {
            return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "User unauthorized.");
        }
    }

    public function getHabitList()
    {
        DB::beginTransaction();
        $record = User::getHabitList();

        DB::commit();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Success", $record);
    }

    public function getActivityList()
    {
        DB::beginTransaction();
        $record = User::getActivityList();

        DB::commit();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Success", $record);
    }

    public function getFieldList()
    {
        DB::beginTransaction();
        $record = User::getFieldList();

        DB::commit();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Success", $record);
    }
}
