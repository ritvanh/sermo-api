<?php
namespace App\Services;
use App\Enums\MessageStatusEnum;
use App\Events\DeleteMessage;
use App\Events\MarkMessagesAsSeen;
use App\Events\SendMessage;
use App\Exceptions\GenericJsonException;
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
        $msg = null;
        $attachmentIds = [];
        DB::transaction(function () use ($myId,$message,&$msg,&$attachmentIds) {
            $msg =  Message::create([
                'sender_id' => $myId,
                'receiver_id' => $message->friendId,
                'message_content' => $message->text,
                'reply_to_id' =>$message->replyToId,
                'status' => MessageStatusEnum::Sent,
                'sent_on' => now(),
                'seen_on' => null
            ]);
            foreach ($message->allFiles() as $file){
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif','txt'];
                $allowedSize = 1024*2048;
                if ($file->getSize() > $allowedSize) {
                    throw new GenericJsonException('File cannot be more than 2 MB',400);
                }
//                if(!in_array($file->getClientOriginalExtension(), $allowedExtensions)){
//                    throw new GenericJsonException('Invalid format of file');
//                }
                $att = MessageAttachment::create([
                    'message_id' => $msg->id,
                    'filename'=>$file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_data' => base64_encode(file_get_contents($file->path()))
                ]);
                array_push($attachmentIds,$att->id);
            }
        });
        $response = [
            'id' => $msg->id,
            'sender_id' => $msg->sender_id,
            'receiver_id' => $msg->receiver_id,
            'message_content' => $msg->message_content,
            'sent_on' => $msg->sent_on,
            'reply_to_id' => $msg->reply_to_id,
            'attachments' => $attachmentIds
        ];
        event(new SendMessage($response));
        return $response;
    }
    public function deleteMessage($myId,$messageId){
        $msg = Message::where('id',$messageId)->first();
        if(!$msg){
            throw new GenericJsonException('Couldnt find message',404);
        }
        if($msg->sender_id != $myId){
            throw new GenericJsonException('You cant delete a message you didnt send',400);
        }
        $deleteMsgDto = [
            'messageId' => $msg->id,
            'friendId' => $msg->sender_id,
            'receiverId' => $msg->receiver_id
        ];
        $msg->delete();
        event(new DeleteMessage($deleteMsgDto));
    }

    public function markMessagesAsSeen($myId, $friendId){
        if(!$this->friendshipService->activeFriendshipExists($myId,$friendId)){
            throw new GenericJsonException('You cant interact with this user',400);
        }
        Message::where([
            ['sender_id',$friendId],
            ['receiver_id',$myId],
            ['status',MessageStatusEnum::Sent]
        ])->update(['status' => MessageStatusEnum::Seen]);
        //broadcast to friendId, update sent messages to seen
        $eventDto = [
            'friendId' => $myId,
            'receiverId' => $friendId
        ];
        event(new MarkMessagesAsSeen($eventDto));
        return true;
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
                $lastMessage = Message::with('attachments')->where([
                    ['sender_id',$myId],
                    ['receiver_id',$friend['id']]
                ])->orWhere([
                    ['sender_id',$friend['id']],
                    ['receiver_id',$myId]
                ])->select('id','status','message_content','sent_on',
                    DB::raw("(CASE WHEN sender_id = $myId THEN true ELSE false END) as is_mine"))
                    ->orderBy('sent_on', 'desc')
                    ->first();
                $unseenCount = Message::where([
                    ['sender_id',$friend['id']],
                    ['receiver_id',$myId],
                    ['status',MessageStatusEnum::Sent]
                ])->count();
                $obj = [
                    'friend' => $user,
                    'lastMessage' => [
                        'id' => $lastMessage['id'],
                        'status' => $lastMessage['status'],
                        'message_content' => count($lastMessage['attachments'])>0 ? 'attachments' : $lastMessage['message_content'],
                        'sent_on' => $lastMessage['sent_on'],
                        'is_mine' => $lastMessage['is_mine']
                    ],
                    'unseenCount' => $unseenCount
                ];
                array_push($activeConvos,$obj);
            }
        }
        return $activeConvos;
    }
    public function getAttachment($attachmentId,$myId){

        $attachment =  MessageAttachment::with('message')->where('id',$attachmentId)->first();
        $correspondingMsg = Message::where('id',$attachment->message_id)->first();
        if($correspondingMsg->sender_id != $myId && $correspondingMsg->receiver_id != $myId){
            throw new GenericJsonException('this attachment doesnt belong to you',403);
        }
        return $attachment;
    }
}
