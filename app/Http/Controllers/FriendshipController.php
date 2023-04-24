<?php
namespace App\Http\Controllers;

use App\Exceptions\GenericJsonException;
use App\Services\FriendshipService;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    protected FriendshipService $friendshipService;

    public function __construct(FriendshipService $friendshipService)
    {
        $this->friendshipService = $friendshipService;
    }

    public function interact(Request $request){
        if(auth()->id() == $request->friendId){
            throw new GenericJsonException("you cant interact with yourself",400);
        }
        $this->friendshipService->interactWithUserFriendship(auth()->id(),$request->friendId,$request->interactionType);
    }
    public function getFriends(Request $request){
        return $this->friendshipService->getFriendsByUserId(auth()->id());
    }
    public function getBlockList(Request $request){
        return $this->friendshipService->getBlockListByUserId(auth()->id());
    }
    public function getFriendRequests(Request $request){
        return $this->friendshipService->getFriendRequests(auth()->id());
    }
    public function search(Request $request){
        return $this->friendshipService->searchProfile($request->query('keyword'),auth()->id());
    }

}
