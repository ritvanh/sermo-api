<?php
namespace App\Services;

use App\Enums\FriendshipStatusEnum;
use App\Exceptions\GenericJsonException;
use App\Models\ProfileView;
use App\Models\User;
use Carbon\Carbon;
use PhpParser\Node\Scalar\String_;

class UserProfileService{
    protected FriendshipService $friendshipService;
    public function __construct(FriendshipService $fs)
    {
        $this->friendshipService = $fs;
    }
    public function addProfileView($viewerId,$viewedId){
        return ProfileView::create([
            'visitor_id' => $viewedId,
            'visited_id' => $viewedId,
            'visited_at' => Carbon::now()
        ]);
    }
    public function getProfileViewsByUserId($userId){
        return ProfileView::where('visited_id',$userId)
            ->select(['visitor_id.id as id','visitor_id.name as name','visitor_id.profilePhotoPath as avatar','visited_at as time'])
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
       // $this->addProfileView($myId,$userId);
        return [
          'name' => $user->name,
            'id' => $user->id,
            'avatar' => $user->profilePhotoPath,
            'relation' => $relationship
        ];
    }

}
