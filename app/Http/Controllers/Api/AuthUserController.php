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
use App\Http\Responses\BaseResponse;
use App\Models\Notification;
use App\Models\UserOtp;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthUserController extends Controller
{
    private $currentUser;

    function __construct()
    {   
        $this->currentUser = auth('api')->user();
    }

    public function register(UserRegisterRequest $request): mixed
    {
        DB::beginTransaction();
        $user = User::userSignup($request);
        if ($user) {
            $token = auth('api')->login($user);
            if ($token) {
                DB::commit();
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Register successfully.", $user, $token);
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

        if (auth('api')->check()) {
            $agent = auth('api')->user();
            $agent->fcm_token = $request->fcm_token;
            $agent->device_id = $request->device_id;
            $agent->device_type = $request->device_type;
            $agent->save();
        }

        if ($agent && $token) {
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Logged in successfully.", $agent, $token);
        }
    }

    function getUserProfile($id)
    {
        $record = User::getUserById($id);

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

    public function forgot(ForgotPasswordRequest $request)
    {
        DB::beginTransaction();
        $user = User::where('mobile_no', $request->mobile_no);
        if ($user->count()) {
            $user = $user->first();
            $token = auth('api')->fromUser($user);
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Success", "", $token);
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

    public function addUserContacts(Request $request)
    {
        DB::beginTransaction();
        $user_id = $this->currentUser->id;
        $user = User::addContacts($request, $user_id);
        
        DB::commit();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Contacts added successfully.", $user);

    }

    public function getUserContacts()
    {
        $contacts = \DB::table('user_contacts')->where('user_id',$this->currentUser->id )->get();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Success", $contacts);
        
    }

    public function getUserContactsById($id)
    {
        $contacts = \DB::table('user_contacts')->where('id', $id)->first();

        // Check if contact was found
        if (!$contacts) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Contact not found", null);
        }
        $contactsArray = (array) $contacts;
    
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Success", $contactsArray);
        
    }

    public function updateUserContacts(Request $request, $id)
    {
        DB::beginTransaction();
        $user_id = $this->currentUser->id;
        $params = $request->all();
        User::updateContacts($id, $user_id, $params);
        
        $contacts = \DB::table('user_contacts')->where('user_id',$this->currentUser->id )->get();
        DB::commit();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Contacts updated successfully.", $contacts);
    }

    public function deleteUserContacts($id)
    {
        \DB::table('user_contacts')->where('id', $id)->delete();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Contact deleted successfully.");
    }

    public function sendSOS()
    {
        // Get the current user's contacts along with their phone numbers
        $contacts = \DB::table('user_contacts')
            ->where('user_id', $this->currentUser->id)
            ->pluck('mobile_no'); // Get only the phone numbers as an array

        $users = User::whereIn('mobile_no', $contacts)->get();

        $message = "{$this->currentUser->first_name} needs your help!";

        // Send notification to each user or batch if applicable
        $this->pushNotification($this->currentUser, $users, $message, "SOS Alert");

        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "SOS notifications sent successfully.");
    }

    
    static function pushNotification($currentUser, $otherUsers, $message, $title, $agoraData = null, $notificationType = '', $modelId = null)
    {
        // Ensure we're working with a collection for consistency
        if (!($otherUsers instanceof \Illuminate\Support\Collection)) {
            $otherUsers = collect([$otherUsers]); // Wrap single user in a collection
        }

        foreach ($otherUsers as $otherUser) {
            if (empty($otherUser->longitude) || empty($otherUser->latitude)) {
                \Log::warning("Missing location data for user ID: {$otherUser->id}");
                continue; // Skip users without location data
            }

            $extras = [
                'notification_type' => $notificationType,
                'model_id' => (string) $modelId,
                'message' => $message,
                'sender_id' => $currentUser->id,
                'notify_user_type' => "user",
                'other_user_type' => "user",
                'longitude' => $currentUser->longitude,
                'latitude' => $currentUser->latitude,
            ];

            if (!empty($otherUser->fcm_token)) {
                $tokens = [$otherUser->id => $otherUser->fcm_token];

                sendPushNotification(
                    $title,
                    $message,
                    $tokens,
                    $extras,
                    true
                );
            } else {
                \Log::warning("FCM token missing for user ID: {$otherUser->id}");
            }
        }
    }

    public function getNotifications()
    {
        $notifications = Notification::where('notify_user_id', $this->currentUser->id )->get();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "SOS notifications sent successfully.", $notifications);

    }
   

}
