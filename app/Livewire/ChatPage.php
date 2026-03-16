<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChatPage extends Component
{
    public ?int $activeConversationId = null;
    public string $messageBody = '';
    public string $search = '';
    public bool $showListOnMobile = true;

    public function mount(): void
    {
        $conversationId = request()->query('conversation');
        if ($conversationId && $this->conversationBelongsToUser((int) $conversationId)) {
            $this->activeConversationId = (int) $conversationId;
            $this->showListOnMobile = false;

            return;
        }

        $firstConversation = $this->conversations()->first();
        if ($firstConversation) {
            $this->activeConversationId = $firstConversation->id;
            $this->showListOnMobile = false;
        }
    }

    protected function conversationBelongsToUser(int $conversationId): bool
    {
        return Conversation::where('id', $conversationId)
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->exists();
    }

    public function conversations()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return Conversation::query()->whereRaw('1 = 0');
        }

        return $user->conversations()
            ->with(['latestMessage.sender', 'users'])
            ->whereHas('messages')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', $term)
                        ->orWhereHas('users', fn ($u) => $u->where('username', 'like', $term));
                });
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('conversations.created_at');
    }

    public function getActiveConversationProperty(): ?Conversation
    {
        if (! $this->activeConversationId) {
            return null;
        }

        return Conversation::with(['messages.sender', 'users'])
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->find($this->activeConversationId);
    }

    public function selectConversation(int $conversationId): void
    {
        $belongsToUser = Conversation::where('id', $conversationId)
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->exists();

        if ($belongsToUser) {
            $this->activeConversationId = $conversationId;
            $this->reset('messageBody');
            $this->showListOnMobile = false;
        }
    }

    public function showList(): void
    {
        $this->showListOnMobile = true;
    }

    public function sendMessage(): void
    {
        $this->validate([
            'messageBody' => ['required', 'string', 'max:1000'],
        ]);

        if (! $this->activeConversationId) {
            return;
        }

        $conversation = Conversation::where('id', $this->activeConversationId)
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->firstOrFail();

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => $this->messageBody,
        ]);

        $conversation->forceFill([
            'last_message_at' => now(),
        ])->save();

        $this->reset('messageBody');

        $this->dispatch('$refresh');
    }

    public function deleteMessage(int $messageId): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $message = Message::query()
            ->where('id', $messageId)
            ->whereHas('conversation.users', fn ($q) => $q->where('users.id', $user->id))
            ->first();

        if (! $message) {
            return;
        }

        if ($message->sender_id !== $user->id) {
            return;
        }

        $message->delete();
    }

    public function deleteConversation(int $conversationId): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $conversation = Conversation::query()
            ->where('id', $conversationId)
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->first();

        if (! $conversation) {
            return;
        }

        $conversation->delete();

        if ($this->activeConversationId === $conversationId) {
            $this->activeConversationId = null;

            $next = $this->conversations()->first();
            if ($next) {
                $this->activeConversationId = $next->id;
            }
        }

        $this->showListOnMobile = true;
    }

    public function render()
    {
        return view('livewire.chat-page', [
            'conversations' => $this->conversations()->get(),
            'activeConversation' => $this->active_conversation,
        ]);
    }
}

