<?php
namespace App\Services;

use App\Enums\FriendshipStatusEnum;
use App\Exceptions\GenericJsonException;
use App\Models\ProfileView;
use App\Models\User;
use Carbon\Carbon;

class UserProfileService{
    protected FriendshipService $friendshipService;
    public function __construct(FriendshipService $fs)
    {
        $this->friendshipService = $fs;
    }
    public function addProfileView($viewerId,$viewedId){
        return ProfileView::create([
            'visitor_id' => $viewerId,
            'visited_id' => $viewedId,
            'visited_at' => now()
        ]);
    }
    public function getProfileViewsByUserId($userId){
        return ProfileView::where('visited_id',$userId)
            ->select(['users.id','users.name','users.profilePhotoPath as avatar','visited_at'])
            ->join('users','users.id','=','profile_views.visitor_id')
            ->get();
    }

    public function getProfileByUserId($myId,$userId){
        $friendship = $this->friendshipService->getFriendship($myId,$userId);
        if(!$friendship){
            $relationship = FriendshipStatusEnum::None;
        }else {
            switch ($friendship->status) {
                case(FriendshipStatusEnum::Blocked):
                    throw new GenericJsonException('User could not be found', 404);
                case(FriendshipStatusEnum::Active):
                    $relationship = FriendshipStatusEnum::Active;
                    break;
                case(FriendshipStatusEnum::Pending):
                    $relationship = FriendshipStatusEnum::Pending;
                    break;
                default:
                    throw new GenericJsonException('Could not define relationship', 500);
            }
        }
        $user = User::where('id',$userId)->first();
        if(!$user){
            throw new GenericJsonException("User not found",404);
        }
       $this->addProfileView($myId,$userId);
        return [
          'name' => $user->name,
            'id' => $user->id,
            'avatar' => $user->profilePhotoPath,
            'relation' => $relationship
        ];
    }

}
