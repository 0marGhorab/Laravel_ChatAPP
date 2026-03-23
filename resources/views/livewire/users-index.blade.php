<div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Users
        </h2>
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search by username or email..."
            class="w-[40%] min-w-0 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition duration-200"
        />
    </div>

    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($users as $user)
            <div class="px-4 py-3 flex items-center justify-between gap-3 text-sm">
                <div class="flex items-center gap-3 min-w-0">
                    <x-user-avatar :user="$user" class="w-10 h-10" />
                    <div class="min-w-0">
                    <div class="font-medium text-gray-900 dark:text-gray-100 truncate">
                        {{ $user->username }}
                    </div>
                    <div class="text-xs text-gray-500 truncate">
                        {{ $user->email }}
                    </div>
                    </div>
                </div>
                <div>
                    @if ($user->id !== auth()->id())
                        <a
                            href="{{ route('chats.start', $user) }}"
                            class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition duration-200"
                        >
                            Start Chat
                        </a>
                    @else
                        <span class="text-xs text-gray-400">(you)</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                @if ($search !== '')
                    No users match "{{ $search }}".
                @else
                    No users found.
                @endif
            </div>
        @endforelse
    </div>
</div>
