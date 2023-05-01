<?php
namespace App\Services;
use App\Enums\FriendshipStatusEnum;
use App\Enums\MessageStatusEnum;
use App\Exceptions\GenericJsonException;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use App\Models\MessageAttachment;
use Illuminate\Support\Facades\DB;

class MessageService{
    protected FriendshipService $friendshipService;
    public function __construct(FriendshipService $service)
    {
        $this->friendshipService = $service;
    }

    public function sendMessage($myId,$message){
        if(!$this->friendshipService->activeFriendshipExists($myId,$message->friendId)){
            throw new GenericJsonException('You cant send a message to this user',400);
        }
        if(!$message->text && !$message->allFiles()){
            throw new GenericJsonException("invalid message", 400);
        }
        //here you save and validate attachments
        DB::transaction(function () use ($myId,$message) {
            $msg =  Message::create([
                'sender_id' => $myId,
                'receiver_id' => $message->friendId,
                'message_content' => $message->text,
                'reply_to_id' =>$message->replyToId,
                'status' => MessageStatusEnum::Sent,
                'sent_on' => now(),
                'seen_on' => null
            ]);
            foreach ($message->allFiles() as $f){
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif','txt'];
                $allowedSize = 1024*2048;
                if ($f->getSize() > $allowedSize) {
                    throw new GenericJsonException('File cannot be more than 2 MB',400);
                }
                if(!in_array($f->getClientOriginalExtension(), $allowedExtensions)){
                    throw new GenericJsonException('Invalid format of file');
                }
                $filePath = $f->store('public/Media');
                MessageAttachment::create([
                    'message_id' => $msg->id,
                    'file_path' => '/storage'.substr($filePath,6,strlen($filePath))
                ]);
            }
            return $msg;
        });

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
        if(!$this->friendshipService->activeFriendshipExists($myId,$friendId)){
            throw new GenericJsonException('You cant view the conversation with this user',400);
        }
        return Message::with('attachments')->where([
            ['sender_id',$myId],
            ['receiver_id',$friendId]
        ])->orWhere([
            ['sender_id',$friendId],
            ['receiver_id',$myId]
        ])->select('id','status','reply_to_id','message_content','sent_on','seen_on',
            DB::raw("(CASE WHEN sender_id = $myId THEN true ELSE false END) as isMine"))
            ->paginate($pageSize,['*'],'',$page);
    }

    public function  getConversations($myId){
        $friends = $this->friendshipService->getFriendsByUserId($myId);
        $activeConvos = [];
        foreach ($friends as $friend){
            if(Message::where([
                ['sender_id',$myId],
                ['receiver_id',$friend['id']]
            ])->orWhere([
                ['sender_id',$friend['id']],
                ['receiver_id',$myId]
            ])->exists()){
                $user = User::where('id',$friend['id'])
                    ->select('id','name','profilePhotoPath as avatar','bio')
                    ->first();
                array_push($activeConvos,$user);
            }
        }
        return $activeConvos;
    }
}
