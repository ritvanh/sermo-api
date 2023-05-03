<?php
namespace App\Http\Controllers;

use App\Events\SendMessage;
use App\Exceptions\GenericJsonException;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected MessageService $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function sendMessage(Request $request)
    {
        $message = $request;
        $msg = $this->messageService->sendMessage(auth()->id(),$message);
        return $msg;
    }
    public function deleteMessage(Request $request){
        return $this->messageService->deleteMessage(auth()->id(),$request->query('id'));
    }
    public function markMessagesAsSeen(Request $request){
        $friendId = $request->query('friendId');
        if(!$friendId){
            throw new GenericJsonException('Invalid conversation',400);
        }
        return $this->messageService->markMessagesAsSeen(auth()->id(),$friendId);
    }
    public function getPaginatedMessages(Request $request){
        return $this->messageService->getMessages(auth()->id(),$request->query('friendId'),$request->query('page'),$request->query('pageSize'));
    }
    public function getConversations(Request $request){
        return $this->messageService->getConversations(auth()->id());
    }
    public function getAttachment(Request $request){
        $id = $request->query('id');
        return $this->messageService->getAttachment($id,auth()->id());
    }
}
