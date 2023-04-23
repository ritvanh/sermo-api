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

    public function sendMessage(Request $request){
        return $this->messageService->sendMessage(auth()->id(),$request->friendId,$request->message);
    }
    public function deleteMessage(Request $request){
        return $this->messageService->deleteMessage(auth()->id(),$request->query('id'));
    }
    public function getPaginatedMessages(Request $request){
        return $this->messageService->getMessages(auth()->id(),$request->query('friendId'),$request->query('page'),$request->query('pageSize'));
    }
}
