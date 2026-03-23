<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateGroup extends Component
{
    use WithFileUploads;

    public string $groupName = '';

    /** @var array<int, int|string> */
    public array $selectedUserIds = [];

    public string $search = '';

    public $photo = null;

    public function createGroup(): void
    {
        $this->validate([
            'groupName' => ['required', 'string', 'max:100'],
            'selectedUserIds' => ['required', 'array', 'min:1'],
            'selectedUserIds.*' => ['integer', Rule::exists('users', 'id')],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $me = Auth::id();
        $ids = collect($this->selectedUserIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->filter(fn (int $id) => $id !== $me)
            ->values();

        if ($ids->isEmpty()) {
            $this->addError('selectedUserIds', __('Select at least one other person.'));

            return;
        }

        $conversation = Conversation::create([
            'title' => $this->groupName,
            'is_group' => true,
            'created_by' => $me,
            'last_message_at' => null,
        ]);

        $now = now();
        $attach = [];
        foreach ($ids->merge([$me]) as $userId) {
            $attach[$userId] = ['last_read_at' => $now];
        }

        $conversation->users()->attach($attach);

        if ($this->photo) {
            $path = $this->photo->store('group-photos', 'public');
            $conversation->forceFill(['avatar_path' => $path])->save();
            $this->photo = null;
        }

        $this->redirect(route('chats', ['conversation' => $conversation->id], false));
    }

    public function getUsersProperty()
    {
        $me = Auth::id();

        return User::query()
            ->where('id', '!=', $me)
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->where('username', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('username')
            ->get();
    }

    public function render()
    {
        return view('livewire.create-group', [
            'users' => $this->users,
        ]);
    }
}
