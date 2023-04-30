<?php
namespace App\Http\Controllers;

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
    public function getPaginatedMessages(Request $request){
        return $this->messageService->getMessages(auth()->id(),$request->query('friendId'),$request->query('page'),$request->query('pageSize'));
    }
    public function getConversations(Request $request){
        return $this->messageService->getConversations(auth()->id());
    }
}
