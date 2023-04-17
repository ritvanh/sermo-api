<?php
namespace App\Services;
use App\Enums\MessageStatusEnum;
use App\Exceptions\GenericJsonException;
use App\Models\Message;
use Carbon\Carbon;

class MessageService{
    protected $friendshipService;
    public function __construct(FriendshipService $service)
    {
        $this->friendshipService = $service;
    }

    public function findOrCreateConversation($myId,$friendId){

    }
    public function sendMessage($myId,$friendId,$message){
        if(!$this->friendshipService->activeFriendshipExists($myId,$friendId)){
            throw new GenericJsonException('You cant send a message to this user',400);
        }
        //here you save and validate attachments

        $msg =  Message::create([
            'sender_id' => $myId,
            'receiver_id' => $friendId,
            'message_content' => $message->text,
            'reply_to_id' =>$message->replyToId,
            'status' => MessageStatusEnum::Sent,
            'sent_on' => Carbon::now(),
            'seen_on' => null
        ]);
        //broadcast here
    }
    public function deleteMessage($myId,$messageId){
        $msg = Message::where('id',$messageId)->first();
        if(!$msg){
            throw new GenericJsonException('Couldnt find message',404);
        }
        if($msg->sender_id != $myId){
            throw new GenericJsonException('You cant delete a message you didnt send',400);
        }
        $msg->delete();
        //broadcast here
    }

    public function markMessageAsSeen($myId, $messageId){
        $msg = Message::where('id',$messageId)->first();
        if(!$msg){
            throw new GenericJsonException('Couldnt find message',404);
        }
        if($msg->receiver_id != $myId){
            throw new GenericJsonException('You cant change the status of a message you send',400);
        }
        if($msg->status != MessageStatusEnum::Sent){
            throw new GenericJsonException("You cant mark a message as read at this moment",400);
        }
        $msg->status = MessageStatusEnum::Seen;
        $msg->save();
        //broadcast
    }
    public function getMessages($myId,$friendId,$page,$pageSize){
        return Message::with()->where([
            ['sender_id',$myId],
            ['receiver_id',$friendId]
        ])->orWhere([
            ['sender_id',$myId],
            ['receiver_id',$friendId]
        ])->paginate($pageSize,['*'],'',$page);
    }
}
