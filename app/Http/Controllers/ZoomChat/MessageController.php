<?php

namespace App\Http\Controllers\ZoomChat;

use App\Http\Controllers\Controller;
use App\Models\ZoomChatMessage;
use App\Models\ZoomChatResponder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $query = ZoomChatMessage::with('responder')
            ->latest();

        if ($request->filled('responder_id')) {
            $query->where('zoom_chat_responder_id', $request->input('responder_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $messages = $query->paginate(50);
        $responders = ZoomChatResponder::pluck('display_name', 'id');

        return view('zoom-chat.messages.index', compact('messages', 'responders'));
    }

    public function show(ZoomChatMessage $message): View
    {
        $message->load('responder');

        return view('zoom-chat.messages.show', compact('message'));
    }
}
