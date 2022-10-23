<?php

namespace Messenger\Chat\Controllers;

use App\Http\Controllers\Controller;
use Messenger\Chat\Models\ConversationUser;
use Messenger\Chat\Models\MessageUser;
use Messenger\Chat\Traits\UploadFile;

class ConversationController extends Controller
{
    use UploadFile;

    public function index()
    {
        if (request()->ajax()) return $this->conversations();
        return view('messenger.index');
    }

    public function conversations()
    {
        $users = config('messenger.model')::exceptAuth()->search()
                        ->hasConversationWithAuth()
                        ->with([
                            'conversations' => function($query) { $query->onlyWithAuth(); }
                        ])->paginate(8);

        $next_page = $users->currentPage() + 1;
        $next_page = $next_page <= $users->lastPage() ? $next_page : null;

        $users = $users->sortByDesc(function($user) {
            if (isset($user->conversations[0]))
                return $user->conversations[0]->last_message_id;
        });

        return response()->json([
            'view' => view('messenger.includes.conversations', compact('users'))->render(),
            'next_page' => $next_page
        ]);
    }

    public function singleConversation($id)
    {
        $users = config('messenger.model')::where('id', $id)
                        ->with([
                            'conversations' => function($query) { $query->onlyWithAuth(); }
                        ])->get();

        return response()->json([
            'view' => view('messenger.includes.conversations', compact('users'))->render()
        ]);
    }

    public function users()
    {
        $users = config('messenger.model')::exceptAuth()->search()->orderBy('last_seen', 'DESC')->paginate(8);

        $next_page = $users->currentPage() + 1;
        $next_page = $next_page <= $users->lastPage() ? $next_page : null;

        return response()->json([
            'view' => view('messenger.includes.users', compact('users'))->render(),
            'next_page' => $next_page
        ]);
    }

    public function updateLastSeen()
    {
        config('messenger.model')::find(request('user_id'))->makeOflline();
        return 'updated';
    }

    public function userDetails($id)
    {
        $user = config('messenger.model')::findOrFail($id);
        return view('messenger.includes.show', compact('user'));
    }

    public function destroy($conversation_id)
    {
        ConversationUser::where(['conversation_id' => $conversation_id, 'user_id' => auth()->id()])->delete();
        MessageUser::where('user_id', auth()->id())->whereHas('message', function($query) use($conversation_id) {
            $query->where('conversation_id', $conversation_id);
        })->forceDelete();
        return back();
    }
}
