<?php

namespace Messenger\Chat\Controllers;

use App\Http\Controllers\Controller;
use Messenger\Chat\Events\MessageCreated;
use Messenger\Chat\Requests\MessageRequest;
use Messenger\Chat\Models\Conversation;
use Messenger\Chat\Models\Message;
use Messenger\Chat\Models\MessageUser;
use Messenger\Chat\Traits\UploadFile;
use Illuminate\Support\Facades\DB;
use Messenger\Chat\Models\ConversationUser;
use Throwable;

class MessageController extends Controller
{
    use UploadFile;

    public function index($id)
    {
        $user = config('messenger.model')::findOrFail($id);
        $conversation = auth()->user()->conversations()->whereHas('users', function($query) use($user) {
                                $query->where('user_id', $user->id);
                            })
                            ->with([
                                'users' => function($query) {
                                    $query->where('user_id', '<>', auth()->id());
                            }])->first();


        if ($conversation) {
            $unread_messages = $this->getUnreadMessages($conversation->id);
            $result = $this->getMessages($conversation->id);
            $this->makeReadMessages($conversation->id);
        } else {
            $result = ['messages' => [], 'next_page' => null];
            $unread_messages = [];
            $conversation = new Conversation();
        }

        return response()->json([
                                'view'            => view('messenger.chat-window.index', compact('conversation', 'user'))->render(),
                                'conversation'    => $conversation,
                                'unread_messages' => $unread_messages,
                                'messages'        => $result['messages'],
                                'next_page'       => $result['next_page'],
                            ], 200);
    }

    public function getUnreadMessages($conversation)
    {
        return Message::where('conversation_id', $conversation)->unreadMessages()->orderBy('created_at', 'ASC')->get();
    }

    public function getMessages($conversation)
    {
        $messages = Message::where('conversation_id', $conversation)->readMessages()->orderBy('created_at', 'DESC')->paginate(10);

        $next_page = $messages->currentPage() + 1;
        $next_page = $next_page <= $messages->lastPage() ? $next_page : null;

        return ['messages' => $messages, 'next_page' => $next_page];
    }

    public function store(MessageRequest $request)
    {
        DB::beginTransaction();
        try {
            $conversation = $this->getConversation($request->conversation_id, $request->user_id);

            $data = $request->message;
            $type = 'text';
            if ($request->file) {
                $type = $request->file->getMimeType();
                if (stripos($request->file->getMimeType(), 'image') !== false) {
                    [$width, $height] = getimagesize($request->file);
                    $data = $this->uploadImage($request->file, 'messages', $width, $height, false, 50);
                } else {
                    $data = $this->uploadImage($request->file, 'messages', null, null);
                }
            }

            $message = $conversation->messages()->create([
                'user_id' => auth()->id(),
                'type'    => $type,
                'message' => $data,
            ]);

            $message->users()->attach([
                auth()->id() => ['read_at' => now()],
                $request->user_id => ['read_at' => null],
            ]);

            $conversation->update(['last_message_id' => $message->id]);
            DB::commit();

            $message->load(['user', 'conversation', 'conversation.users' => function($query) { $query->where('user_id', '<>', auth()->id()); }]);

            broadcast(new MessageCreated($message, $request->user_id));
            return [
                'user_id'    => $request->user_id,
                'message' => $message
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            dd($e);
        }
    }

    public function updateReadAt()
    {
        $this->makeReadMessages(request('conversation_id'));
        return 'updated';
    }

    protected function getConversation($conversation_id = null, $user_id = null)
    {
        if ($conversation_id) {
            $conversation = auth()->user()->conversations()->find($conversation_id);
        } else {
            $conversation = auth()->user()->conversations()
                                ->where('type', 'peer')
                                ->whereHas('users', function($query) use($user_id) {
                                    $query->where('user_id', $user_id);
                                })->first();
        }

        if (! $conversation) {
            $conversation = Conversation::create(['user_id' => auth()->id()]);
            $conversation->users()->attach([auth()->id(), $user_id]);
        } else {
            ConversationUser::where('conversation_id', $conversation_id)->onlyTrashed()->restore();
        }

        return $conversation;
    }

    protected function makeReadMessages($conversation_id)
    {
        MessageUser::whereNull('read_at')->where('user_id', auth()->id())->whereHas('message', function($query) use($conversation_id) {
                        $query->where('conversation_id', $conversation_id);
                    })->update(['read_at' => now()]);
    }

    public function delete($message_id, $user_id = null)
    {
        $query = MessageUser::where('message_id', $message_id);

        if ($user_id) {
            $query->where('user_id', $user_id)->forceDelete();
        } else {
            $update_seen = $query;
            $update_seen->update(['read_at' => now()]);
            $query->delete();
        }

        return response()->json(['message' => 'Message Deleted'], 200);
    }
}
