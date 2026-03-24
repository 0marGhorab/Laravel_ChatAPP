<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChatPage extends Component
{
    use WithFileUploads;

    public ?int $activeConversationId = null;

    public string $messageBody = '';

    public string $search = '';

    public bool $showListOnMobile = true;

    public $groupPhoto = null;

    public $voiceNote = null;

    public string $groupTitle = '';

    public function mount(): void
    {
        $conversationId = request()->query('conversation');
        if ($conversationId && $this->conversationBelongsToUser((int) $conversationId)) {
            $this->activeConversationId = (int) $conversationId;
            $this->showListOnMobile = false;
            $this->markConversationAsRead($this->activeConversationId);
            $this->syncGroupTitleFromActiveConversation();

            return;
        }

        $firstConversation = $this->conversations()->first();
        if ($firstConversation) {
            $this->activeConversationId = $firstConversation->id;
            $this->showListOnMobile = false;
            $this->markConversationAsRead($this->activeConversationId);
            $this->syncGroupTitleFromActiveConversation();
        }
    }

    protected function syncGroupTitleFromActiveConversation(): void
    {
        if (! $this->activeConversationId) {
            $this->groupTitle = '';

            return;
        }

        $conversation = Conversation::query()
            ->where('id', $this->activeConversationId)
            ->first();

        $this->groupTitle = (string) ($conversation?->title ?? '');
    }

    protected function conversationBelongsToUser(int $conversationId): bool
    {
        return Conversation::where('id', $conversationId)
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->exists();
    }

    public function conversations()
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return Conversation::query()->whereRaw('1 = 0');
        }

        $userId = $user->id;

        return $user->conversations()
            ->where(function ($q) {
                $q->whereHas('messages')
                    ->orWhere('conversations.is_group', true);
            })
            ->with(['latestMessage.sender', 'users'])
            ->withCount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query->where('sender_id', '!=', $userId)
                        ->whereRaw(
                            'messages.created_at > COALESCE((
                                SELECT cu.last_read_at FROM conversation_user AS cu
                                WHERE cu.conversation_id = messages.conversation_id AND cu.user_id = ?
                                LIMIT 1
                            ), ?)',
                            [$userId, '1970-01-01 00:00:00']
                        );
                },
            ])
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
            $this->reset('messageBody', 'groupPhoto', 'voiceNote');
            $this->syncGroupTitleFromActiveConversation();
            $this->showListOnMobile = false;
            $this->markConversationAsRead($conversationId);
        }
    }

    /**
     * Mark messages in this conversation as read for the current user (sidebar unread badge).
     */
    protected function markConversationAsRead(?int $conversationId): void
    {
        if (! $conversationId) {
            return;
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if (! $user->conversations()->where('conversations.id', $conversationId)->exists()) {
            return;
        }

        $user->conversations()->updateExistingPivot($conversationId, [
            'last_read_at' => now(),
        ]);
    }

    /**
     * While viewing a thread, keep last_read_at current and reload messages.
     */
    public function pollActiveChat(): void
    {
        if ($this->activeConversationId) {
            $this->markConversationAsRead($this->activeConversationId);
        }
    }

    public function showList(): void
    {
        $this->showListOnMobile = true;
    }

    #[On('profile-updated')]
    public function refreshAvatarsAfterProfileUpdate(): void
    {
        // Re-query conversations / senders so avatar URLs match the database.
    }

    public function updatedGroupPhoto(): void
    {
        if (! $this->groupPhoto) {
            return;
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $this->activeConversationId) {
            $this->reset('groupPhoto');

            return;
        }

        $conversation = Conversation::query()
            ->where('id', $this->activeConversationId)
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->first();

        if (! $conversation || ! $user->canManageGroupPhoto($conversation)) {
            $this->reset('groupPhoto');
            $this->addError('groupPhoto', __('You cannot change this group photo.'));

            return;
        }

        $this->validate([
            'groupPhoto' => ['required', 'image', 'max:2048'],
        ]);

        $path = $this->groupPhoto->store('group-photos', 'public');

        if ($conversation->avatar_path) {
            Storage::disk('public')->delete($conversation->avatar_path);
        }

        $conversation->forceFill(['avatar_path' => $path])->save();

        $this->reset('groupPhoto');

        $this->dispatch('$refresh');
        $this->dispatch('toast', message: __('Group photo updated.'));
    }

    public function removeGroupPhoto(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $this->activeConversationId) {
            return;
        }

        $conversation = Conversation::query()
            ->where('id', $this->activeConversationId)
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->first();

        if (! $conversation || ! $user->canManageGroupPhoto($conversation) || ! $conversation->avatar_path) {
            return;
        }

        Storage::disk('public')->delete($conversation->avatar_path);
        $conversation->forceFill(['avatar_path' => null])->save();

        $this->dispatch('$refresh');
        $this->dispatch('toast', message: __('Group photo removed.'));
    }

    public function saveGroupTitle(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $this->activeConversationId) {
            return;
        }

        $conversation = Conversation::query()
            ->where('id', $this->activeConversationId)
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->first();

        if (! $conversation || ! $conversation->is_group || ! $user->canManageGroupPhoto($conversation)) {
            $this->addError('groupTitle', __('You cannot rename this group.'));

            return;
        }

        $validated = $this->validate([
            'groupTitle' => ['required', 'string', 'max:255'],
        ]);

        $conversation->forceFill([
            'title' => trim($validated['groupTitle']),
        ])->save();

        $this->groupTitle = $conversation->title ?? '';

        $this->dispatch('$refresh');
        $this->dispatch('toast', message: __('Group name updated.'));
    }

    public function sendMessage(): void
    {
        $this->validate([
            'messageBody' => ['nullable', 'string', 'max:1000', 'required_without:voiceNote'],
            'voiceNote' => ['nullable', 'file', 'max:10240', 'required_without:messageBody', 'mimes:webm,mp3,mp4,m4a,aac,wav,ogg,oga'],
        ]);

        if (! $this->activeConversationId) {
            return;
        }

        $conversation = Conversation::where('id', $this->activeConversationId)
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->firstOrFail();

        $audioPath = null;

        if ($this->voiceNote) {
            $audioPath = $this->voiceNote->store('voice-notes', 'public');
        }

        $body = trim($this->messageBody);

        if ($body === '' && ! $audioPath) {
            $this->addError('messageBody', __('Type a message or attach a voice note.'));
            $this->addError('voiceNote', __('Attach a voice note or type a message.'));

            return;
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => $body !== '' ? $body : null,
            'audio_path' => $audioPath,
        ]);

        $conversation->forceFill([
            'last_message_at' => now(),
        ])->save();

        $this->reset('messageBody', 'voiceNote');

        $this->dispatch('$refresh');
        $this->dispatch('toast', message: __('Message sent.'));
    }

    public function deleteMessage(int $messageId): void
    {
        /** @var User|null $user */
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

        $conversationId = $message->conversation_id;

        if ($message->audio_path) {
            Storage::disk('public')->delete($message->audio_path);
        }

        $message->delete();

        $conversation = Conversation::query()->find($conversationId);
        if ($conversation && ! $conversation->messages()->exists()) {
            $conversation->delete();
            $this->afterConversationRemoved($conversationId);
        }

        $this->dispatch('$refresh');
        $this->dispatch('toast', message: __('Message deleted.'));
    }

    /**
     * Reselect another chat or clear the panel after a conversation row is gone.
     */
    protected function afterConversationRemoved(int $removedConversationId): void
    {
        if ($this->activeConversationId !== $removedConversationId) {
            return;
        }

        $this->activeConversationId = null;
        $this->reset('messageBody', 'groupPhoto', 'voiceNote');
        $this->groupTitle = '';

        $next = $this->conversations()->first();
        if ($next) {
            $this->activeConversationId = $next->id;
            $this->markConversationAsRead($this->activeConversationId);
            $this->syncGroupTitleFromActiveConversation();
        }

        $this->showListOnMobile = true;
    }

    public function deleteConversation(int $conversationId): void
    {
        /** @var User|null $user */
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

        $this->afterConversationRemoved($conversationId);
        $this->dispatch('toast', message: __('Conversation deleted.'));
    }

    public function render()
    {
        return view('livewire.chat-page', [
            'conversations' => $this->conversations()->get(),
            'activeConversation' => $this->active_conversation,
        ]);
    }
}
