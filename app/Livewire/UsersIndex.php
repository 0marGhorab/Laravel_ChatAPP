<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UsersIndex extends Component
{
    public string $search = '';

    public function getUsersProperty()
    {
        return User::query()
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';
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
        return view('livewire.users-index', [
            'users' => $this->users,
        ]);
    }
}
