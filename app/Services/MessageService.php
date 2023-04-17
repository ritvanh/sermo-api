<?php
namespace App\Services;
class MessageService{
    protected $friendshipService;
    public function __construct(FriendshipService $service)
    {
        $this->friendshipService = $service;
    }

    public function findOrCreateConversation($myId,$friendId){

    }
    public function sendMessage($myId,$messageContent,$conversationId){

    }
}
