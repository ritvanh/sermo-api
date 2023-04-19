<?php
namespace App\Services;

use App\Enums\FriendshipActionEnum;
use App\Enums\FriendshipStatusEnum;
use App\Exceptions\GenericJsonException;
use App\Models\Friendship;
use Carbon\Carbon;

class FriendshipService{

    public function getFriendship($myId, $friendId){
        return Friendship::where([
            ['by_user',$myId],
            ['to_user',$friendId]
        ])->orWhere([
            ['by_user',$friendId],
            ['to_user',$myId]
        ])->first();
    }
    public function activeFriendshipExists($myId, $friendId){
        return Friendship::where([
            ['by_user',$myId],
            ['to_user',$friendId],
            ['status',FriendshipStatusEnum::Active]
        ])->orWhere([
            ['by_user',$friendId],
            ['to_user',$myId],
            ['status',FriendshipStatusEnum::Active]
        ])->exists();
    }
    public function activeBlockExists($myId,$friendId){
        return Friendship::where([
            ['by_user',$myId],
            ['to_user',$friendId],
            ['status',FriendshipStatusEnum::Blocked]
        ])->orWhere([
            ['by_user',$friendId],
            ['to_user',$myId],
            ['status',FriendshipStatusEnum::Blocked]
        ])->exists();
    }

    public function  sendFriendRequest($myId, $friendId){
        $exisitngRelation = $this->getFriendship($myId,$friendId);
        if($exisitngRelation){
            switch ($exisitngRelation->status){
                case(FriendshipStatusEnum::Active):
                    throw new GenericJsonException("You already are friends",400);
                case(FriendshipStatusEnum::Blocked):
                    throw new GenericJsonException("Couldn't find user",404);
                case(FriendshipStatusEnum::Pending):
                    throw new GenericJsonException("This friendship is pending",400);
                default:
                    throw new GenericJsonException("Couldn't identify relationship status",400);
            }
        }
        $friendship = Friendship::create([
            'by_user' => $myId,
            'to_user' => $friendId,
            'status' => FriendshipStatusEnum::Pending,
            'created_on' => Carbon::now()
        ]);
        return true;
    }
    public function  cancelFriendRequest($myId, $friendId){
        $exisitngRelation = $this->getFriendship($myId,$friendId);
        if(!$exisitngRelation){
            throw new GenericJsonException("Couldn't find a relationship with this user",404);
        }
        switch ($exisitngRelation->status){
            case(FriendshipStatusEnum::Blocked):
                throw new GenericJsonException("Couldn't find user",404);
            case(FriendshipStatusEnum::Active):
                throw  new GenericJsonException("Can't cancel an active friendship",400);
            case(FriendshipStatusEnum::Pending):
                if($exisitngRelation->by_user != $myId){
                    throw new GenericJsonException("The request wasn't sent by you",400);
                }
                $exisitngRelation->delete();
                return true;
            default:
                throw new GenericJsonException("Couldn't identify relationship status",400);
        }
    }

    public function acceptFriendRequest($myId, $friendId){
        $exisitngRelation = $this->getFriendship($myId,$friendId);
        if(!$exisitngRelation){
            throw new GenericJsonException("Couldn't find a relationship with this user",404);
        }
        switch ($exisitngRelation->status){
            case(FriendshipStatusEnum::Blocked):
                throw new GenericJsonException("Couldn't find user",404);
            case(FriendshipStatusEnum::Active):
                throw  new GenericJsonException("The friendship is active",400);
            case(FriendshipStatusEnum::Pending):
                if($exisitngRelation->to_user != $myId){
                    throw new GenericJsonException("The request wasn't meant for you",400);
                }
                $exisitngRelation->status = FriendshipStatusEnum::Active;
                $exisitngRelation->save();
                return true;
            default:
                throw new GenericJsonException("Couldn't identify relationship status",400);
        }
    }
    public function refuseFriendRequest($myId, $friendId){
        $exisitngRelation = $this->getFriendship($myId,$friendId);
        if(!$exisitngRelation){
            throw new GenericJsonException("Couldn't find a relationship with this user",404);
        }
        switch ($exisitngRelation->status){
            case(FriendshipStatusEnum::Blocked):
                throw new GenericJsonException("Couldn't find user",404);
            case(FriendshipStatusEnum::Active):
                throw  new GenericJsonException("The friendship is active",400);
            case(FriendshipStatusEnum::Pending):
                if($exisitngRelation->to_user != $myId){
                    throw new GenericJsonException("The request wasn't meant for you",400);
                }
                $exisitngRelation->delete();
                return true;
            default:
                throw new GenericJsonException("Couldn't identify relationship status",400);
        }
    }
    public function endFriendship($myId,$friendId){
        $exisitngRelation = $this->getFriendship($myId,$friendId);
        if(!$exisitngRelation){
            throw new GenericJsonException("Couldn't find a relationship with this user",404);
        }
        switch ($exisitngRelation->status){
            case(FriendshipStatusEnum::Blocked):
                throw new GenericJsonException("Couldn't find user",404);
            case(FriendshipStatusEnum::Active):
                $exisitngRelation->delete();
                return true;
            case(FriendshipStatusEnum::Pending):
                throw new GenericJsonException("The friendship is pendit still",400);
            default:
                throw new GenericJsonException("Couldn't identify relationship status",400);
        }
    }

    public function blockUser($myId,$friendId){
        $exisitngRelation = $this->getFriendship($myId,$friendId);
        if($exisitngRelation){
            if($exisitngRelation->status == FriendshipStatusEnum::Blocked){
                if($exisitngRelation->by_user == $myId){
                    throw new GenericJsonException("You have already blocked this user",400);
                }else{
                    throw new GenericJsonException("Couldn't find user",404);
                }
            }else{
                $exisitngRelation->delete();
            }
        }
        $friendship = Friendship::create([
            'by_user' => $myId,
            'to_user' => $friendId,
            'status' => FriendshipStatusEnum::Blocked,
            'created_on' => Carbon::now()
        ]);
        return true;
    }
    public function unblockUser($myId,$friendId){
        $exisitngRelation = $this->getFriendship($myId,$friendId);
        if(!$exisitngRelation){
            throw new GenericJsonException("A relation could not be found",404);
        }
        if($exisitngRelation->by_user != $myId){
            throw new GenericJsonException("Could not find this user",404);
        }
        if($exisitngRelation->status != FriendshipStatusEnum::Blocked){
            throw new GenericJsonException("This user is not in your block list",400);
        }
        $exisitngRelation->delete();
        return true;
    }
    public function interactWithUserFriendship($myId,$friendId,$interactionType){
        switch ($interactionType){
            case(FriendshipActionEnum::Send):
                $this->sendFriendRequest($myId,$friendId);
                break;
            case(FriendshipActionEnum::Cancel):
                $this->cancelFriendRequest($myId,$friendId);
                break;
            case(FriendshipActionEnum::Accept):
                $this->acceptFriendRequest($myId,$friendId);
                break;
            case(FriendshipActionEnum::Refuse):
                break;
                $this->refuseFriendRequest($myId,$friendId);
            case(FriendshipActionEnum::Unfriend):
                $this->endFriendship($myId,$friendId);
                break;
            case(FriendshipActionEnum::Block):
                $this->blockUser($myId,$friendId);
                break;
            case(FriendshipActionEnum::Unblock):
                $this->unblockUser($myId,$friendId);
                break;
            default:
                throw new GenericJsonException("Couldn't identity action",400);
        }
    }
    public function getFriendsByUserId($myId){
        $friendships = Friendship::with(['by_user','to_user'])->where([
            ['by_user',$myId],
            ['status',FriendshipStatusEnum::Active]
        ])->orWhere([
            ['to_user',$myId],
            ['status',FriendshipStatusEnum::Active]
        ])->get();
        $friends = [];
        foreach ($friendships as $friend){
            if($friend->by_user == $myId){
                $f = [
                    'id'=> $friend->to_user->id,
                    'name' => $friend->to_user->name,
                    'profilePhoto' => $friend->to_user->profilePhotoPath
                ];
                array_push($friends,$f);
            }else{
                $f = [
                    'id'=> $friend->by_user->id,
                    'name' => $friend->by_user->name,
                    'profilePhoto' => $friend->by_user->profilePhotoPath
                ];
                array_push($friends,$f);
            }
        }
        return $friends;
    }
    public function getBlockListByUserId($myId){
        return Friendship::with('to_user')
            ->where([
                ['by_user',$myId],
                ['status',FriendshipStatusEnum::Blocked]
            ])
            ->select(['to_user.id as id','to_user.name as name','to_user.profilePhotoPath as avatar'])
            ->get();
    }
    public function getFriendRequests($myId){
        return Friendship::where([
            ['to_user',$myId],
            ['status',FriendshipStatusEnum::Pending]
        ])->select(['by_user.id as id','by_user.name as name','by_user.profilePhotoPath as avatar'])
            ->get();
    }
}
