<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Find or create a 1-to-1 conversation with the given user, then redirect to chats with that conversation open.
     */
    public function start(User $user): RedirectResponse
    {
        /** @var User $me */
        $me = Auth::user();

        if ($user->id === $me->id) {
            return redirect()->route('chats');
        }

        $conversation = $me->conversations()
            ->where('is_group', false)
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->get()
            ->first(fn ($c) => $c->users->count() === 2);

        if (! $conversation) {
            $conversation = Conversation::create([
                'title' => null,
                'is_group' => false,
                'created_by' => $me->id,
            ]);
            $conversation->users()->attach([$me->id, $user->id]);
        }

        return redirect()->route('chats', ['conversation' => $conversation->id]);
    }
}
