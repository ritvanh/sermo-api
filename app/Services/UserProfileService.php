<?php
namespace App\Services;

use App\Enums\FriendshipStatusEnum;
use App\Exceptions\GenericJsonException;
use App\Models\ProfileView;
use App\Models\User;

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
            $isCreatedByMe = false;
        }else {
            switch ($friendship->status) {
                case(FriendshipStatusEnum::Blocked):
                    if($friendship->by_user != $myId) {
                        throw new GenericJsonException('User could not be found', 404);
                    }
                    $relationship = FriendshipStatusEnum::Blocked;
                    $isCreatedByMe = true;
                    break;
                case(FriendshipStatusEnum::Active):
                    $relationship = FriendshipStatusEnum::Active;
                    $isCreatedByMe = $friendship->by_user == $myId;
                    break;
                case(FriendshipStatusEnum::Pending):
                    $relationship = FriendshipStatusEnum::Pending;
                    $isCreatedByMe = $friendship->by_user == $myId;
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
            'bio' => $user->bio,
            'relation' => $relationship,
            'isRelationCreatedByMe' => $isCreatedByMe
        ];
    }

}
