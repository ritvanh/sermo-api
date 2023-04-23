<?php

namespace App\Http\Controllers;

use App\Services\UserProfileService;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    protected UserProfileService $userProfileService;

    public function __construct(UserProfileService $userService)
    {
        $this->userProfileService = $userService;
    }
    public function getProfile(Request $request){
        $userId = $request->query('id');
        $loggedUserId = auth()->id();
        $result = $this->userProfileService->getProfileByUserId($loggedUserId,$userId);
        return $result;
    }
    public function getProfileViews(Request $request){
        $userId = auth()->id();
        return $this->userProfileService->getProfileViewsByUserId($userId);
    }

}
