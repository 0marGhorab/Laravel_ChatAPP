<div class="h-full">
<div class="flex flex-col lg:flex-row h-[calc(100vh-6rem)] bg-white dark:bg-gray-900 border border-primary/20 dark:border-gray-700 rounded-xl overflow-hidden min-h-0 min-w-0 shadow-lg">
        {{-- Conversations sidebar: below lg = toggle with section; lg+ = side by side --}}
        <aside class="border-r border-primary/20 dark:border-gray-700 flex flex-col w-full min-w-0 lg:min-w-[min(100%,16rem)] lg:max-w-sm xl:max-w-md lg:flex-[1_1_auto] lg:shrink min-h-0 @if(! $showListOnMobile) hidden lg:flex @endif">
            <div class="px-3 sm:px-4 py-3 border-b border-primary/20 dark:border-gray-700 flex items-center gap-2 sm:gap-3 min-w-0">
                <h2 class="shrink-0 font-display text-lg sm:text-xl tracking-wide text-primary">
                    Chats
                </h2>
                <div class="relative min-w-0 flex-1">
                    <input
                        type="text"
                        wire:model.debounce.300ms="search"
                        placeholder="Find Chat..."
                        class="w-full min-w-0 max-w-full box-border px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition duration-200"
                    />
                </div>
                <div x-data="{ addOpen: false }" class="relative shrink-0" @keydown.escape.window="addOpen = false">
                    <button
                        type="button"
                        @click="addOpen = !addOpen"
                        :aria-expanded="addOpen"
                        class="inline-flex items-center justify-center rounded-full border border-primary/30 text-primary hover:bg-primary/10 focus:outline-none focus:ring-2 focus:ring-primary transition duration-200 p-2 sm:p-2.5"
                        title="{{ __('New chat or group') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="8.5" cy="7" r="3.5" />
                            <path d="M20 8v6" />
                            <path d="M23 11h-6" />
                        </svg>
                    </button>
                    <div
                        x-show="addOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        @click.outside="addOpen = false"
                        x-cloak
                        class="absolute right-0 top-full mt-2 w-52 rounded-lg shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 z-40 py-1"
                    >
                        <a
                            href="{{ route('users.index') }}"
                            class="block px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 hover:bg-primary/10 focus:outline-none focus:bg-primary/10"
                        >
                            {{ __('Start chat') }}
                        </a>
                        <a
                            href="{{ route('groups.create') }}"
                            class="block px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 hover:bg-primary/10 focus:outline-none focus:bg-primary/10 border-t border-gray-100 dark:border-gray-700"
                        >
                            {{ __('Create group') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                @forelse ($conversations as $conversation)
                    @php
                        $last = $conversation->latestMessage;
                        $isActive = $activeConversation && $activeConversation->id === $conversation->id;
                    @endphp
                    @php
                        $otherNames = $conversation->users
                            ->where('id', '!=', auth()->id())
                            ->pluck('username')
                            ->implode(', ');
                        $sidebarAvatarUser = $conversation->is_group
                            ? ($last?->sender ?? $conversation->users->firstWhere('id', '!=', auth()->id()))
                            : $conversation->users->firstWhere('id', '!=', auth()->id());
                    @endphp
                    <button
                        wire:click="selectConversation({{ $conversation->id }})"
                        class="w-full text-left px-4 py-3 flex gap-3 border-b border-gray-100 dark:border-gray-800 hover:bg-primary/5 dark:hover:bg-gray-800 transition duration-200 @if($isActive) bg-primary/10 dark:bg-gray-800 border-l-2 border-l-primary @endif"
                    >
                        @if ($conversation->is_group)
                            <x-group-avatar :conversation="$conversation" class="!w-10 !h-10 mt-0.5" />
                        @else
                            <x-user-avatar :user="$sidebarAvatarUser" class="w-10 h-10 mt-0.5" />
                        @endif
                        <div class="min-w-0 flex-1 flex flex-col gap-1">
                        <div class="flex items-center justify-between gap-2 min-w-0">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate @if($isActive) text-primary @endif">
                                {{ $conversation->title ?? ($otherNames !== '' ? $otherNames : 'You') }}
                            </span>
                            <div class="flex items-center gap-1.5 shrink-0">
                                @if (($conversation->unread_count ?? 0) > 0)
                                    <span
                                        class="inline-flex min-w-[1.25rem] h-5 px-1.5 items-center justify-center rounded-full bg-primary text-[10px] font-semibold text-white leading-none"
                                        title="{{ __('Unread messages') }}"
                                    >
                                        {{ ($conversation->unread_count ?? 0) > 99 ? '99+' : $conversation->unread_count }}
                                    </span>
                                @endif
                                @if ($last)
                                    <span class="text-[11px] text-gray-500 whitespace-nowrap">
                                        {{ $last->created_at->diffForHumans(short: true) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if ($last)
                            <p class="text-xs text-gray-600 dark:text-gray-300 truncate">
                                <span class="font-semibold">{{ $last->sender->username ?? 'You' }}:</span>
                                {{ $last->body }}
                            </p>
                        @else
                            <p class="text-xs text-gray-500">No messages yet</p>
                        @endif
                        </div>
                    </button>
                @empty
                    <div class="p-4 text-xs text-gray-500">
                        You have no conversations yet.
                    </div>
                @endforelse
            </div>
        </aside>

        {{-- Messages panel: full width below lg; w-0 flex-1 at lg+ so content cannot stretch --}}
        <section class="w-full lg:w-0 flex-1 min-w-0 flex flex-col min-h-0 overflow-hidden @if($showListOnMobile) hidden lg:flex @endif">
            @if (! $activeConversation)
                <div class="flex-1 flex items-center justify-center text-gray-500 text-sm animate-fade-in">
                    <p class="font-display text-primary/70 text-lg">Select a chat from the left to start messaging.</p>
                </div>
            @else
                @php
                    $activeOtherNames = $activeConversation->users
                        ->where('id', '!=', auth()->id())
                        ->pluck('username')
                        ->implode(', ');
                    $headerAvatarUser = $activeConversation->is_group
                        ? null
                        : $activeConversation->users->firstWhere('id', '!=', auth()->id());
                @endphp
                <header class="px-2 py-3 border-b border-primary/20 dark:border-gray-700 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        @if ($activeConversation->is_group)
                            <x-group-avatar :conversation="$activeConversation" class="!w-10 !h-10 shrink-0" />
                        @elseif ($headerAvatarUser)
                            <x-user-avatar :user="$headerAvatarUser" class="w-10 h-10 shrink-0" />
                        @endif
                        <h2 class="font-display text-lg tracking-wide text-primary truncate">
                            {{ $activeConversation->title ?? ($activeOtherNames !== '' ? $activeOtherNames : 'You') }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-2" x-data="{ chatMenuOpen: false, chatConfirm: false }" @click.outside="chatMenuOpen = false; chatConfirm = false">
                        <div class="relative">
                            <button
                                type="button"
                                @click="chatMenuOpen = !chatMenuOpen; chatConfirm = false"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-secondary hover:bg-secondary/10 focus:outline-none focus:ring-2 focus:ring-secondary"
                                aria-label="Chat options"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="6" r="1.5" />
                                    <circle cx="12" cy="12" r="1.5" />
                                    <circle cx="12" cy="18" r="1.5" />
                                </svg>
                            </button>
                            <div
                                x-show="chatMenuOpen"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                x-cloak
                                class="absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 z-20"
                            >
                                @if ($activeConversation->is_group && auth()->user()->canManageGroupPhoto($activeConversation))
                                    <div class="px-2 py-2 border-b border-gray-100 dark:border-gray-700 space-y-1">
                                        <label for="group-photo-chat-input" class="flex w-full justify-center px-2 py-2 text-xs font-medium text-gray-800 dark:text-gray-100 hover:bg-primary/10 rounded cursor-pointer">
                                            {{ __('Change group photo') }}
                                        </label>
                                        <input id="group-photo-chat-input" type="file" wire:model="groupPhoto" accept="image/*" class="sr-only" />
                                        @if ($activeConversation->avatar_path)
                                            <button
                                                type="button"
                                                wire:click="removeGroupPhoto"
                                                wire:confirm="{{ __('Remove the group photo?') }}"
                                                class="w-full px-2 py-2 text-xs text-gray-700 dark:text-gray-200 hover:bg-primary/10 rounded"
                                            >
                                                {{ __('Remove group photo') }}
                                            </button>
                                        @endif
                                        <div wire:loading wire:target="groupPhoto" class="text-center text-[10px] text-gray-500">
                                            {{ __('Saving…') }}
                                        </div>
                                        @error('groupPhoto')
                                            <p class="text-[10px] text-red-600 px-1 text-center">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif
                                <template x-if="!chatConfirm">
                                    <button
                                        type="button"
                                        @click="chatConfirm = true"
                                        class="w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-t-lg"
                                    >
                                        Delete
                                    </button>
                                </template>
                                <template x-if="chatConfirm">
                                    <div class="px-3 py-2 space-y-2 text-xs">
                                        <p class="text-gray-700 dark:text-gray-300">
                                            Delete this chat and all its messages?
                                        </p>
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                @click="chatMenuOpen = false; chatConfirm = false"
                                                class="px-2 py-1 rounded-full text-[11px] text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="button"
                                                @click="chatMenuOpen = false; chatConfirm = false; $wire.deleteConversation({{ $activeConversation->id }});"
                                                class="px-2 py-1 rounded-full text-[11px] text-white bg-red-600 hover:bg-red-700"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="showList"
                            class="inline-flex lg:hidden items-center px-3 py-1.5 rounded-full border border-primary/30 text-primary bg-white dark:bg-gray-800 hover:bg-primary/10 transition duration-200"
                        >
                            ← Chats
                        </button>
                    </div>
                </header>

                <div
                    class="flex-1 overflow-y-auto px-2 py-4 space-y-3 bg-gray-50 dark:bg-gray-900"
                    wire:poll.2s="pollActiveChat"
                >
                    @forelse ($activeConversation->messages()->with('sender')->latest()->take(100)->get()->reverse() as $message)
                        <div
                            class="flex animate-fade-in w-full @if($message->sender_id === auth()->id()) flex-row-reverse @else flex-row @endif items-end gap-2"
                            @if($loop->last)
                                x-data
                                x-init="$nextTick(() => { $el.scrollIntoView({ behavior: 'smooth', block: 'end' }); })"
                            @endif
                        >
                            @if ($message->sender_id === auth()->id())
                                <x-user-avatar :user="auth()->user()" class="w-8 h-8 mb-1 shrink-0" />
                            @else
                                <x-user-avatar :user="$message->sender" class="w-8 h-8 mb-1 shrink-0" />
                            @endif
                            <div class="flex flex-col min-w-0 max-w-[min(100%,85%)] sm:max-w-[min(100%,45%)] @if($message->sender_id === auth()->id()) items-end @else items-start @endif">
                            <div class="flex items-center gap-2 mb-0.5" x-data="{ menuOpen: false, confirmOpen: false }" @click.outside="menuOpen = false; confirmOpen = false">
                                <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-300 @if($message->sender_id === auth()->id()) text-primary @endif">
                                    {{ $message->sender_id === auth()->id() ? 'You' : ($message->sender->username ?? 'User') }}
                                </span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $message->created_at->format('g:i A') }}</span>
                                @if($message->sender_id === auth()->id())
                                    <div class="relative">
                                        <button
                                            type="button"
                                            @click="menuOpen = !menuOpen; confirmOpen = false"
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-primary"
                                            aria-label="Message options"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                                <circle cx="12" cy="6" r="1.5" />
                                                <circle cx="12" cy="12" r="1.5" />
                                                <circle cx="12" cy="18" r="1.5" />
                                            </svg>
                                        </button>
                                        <div
                                            x-show="menuOpen"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            x-cloak
                                            class="absolute right-0 mt-1 w-40 rounded-lg shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 z-20"
                                        >
                                            <template x-if="!confirmOpen">
                                                <button
                                                    type="button"
                                                    @click="confirmOpen = true"
                                                    class="w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-t-lg"
                                                >
                                                    Delete message
                                                </button>
                                            </template>
                                            <template x-if="confirmOpen">
                                                <div class="px-3 py-2 space-y-2 text-xs">
                                                    <p class="text-gray-700 dark:text-gray-300">
                                                        Delete this message?
                                                    </p>
                                                    <div class="flex items-center justify-end gap-2 p-2">
                                                        <button
                                                            type="button"
                                                            @click="menuOpen = false; confirmOpen = false"
                                                            class="px-2 py-1 rounded-full text-[11px] text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                        >
                                                            Cancel
                                                        </button>
                                                        <button
                                                            type="button"
                                                            @click="menuOpen = false; confirmOpen = false; $wire.deleteMessage({{ $message->id }});"
                                                            class="px-2 py-1 rounded-full text-[11px] text-white bg-red-600 hover:bg-red-700"
                                                        >
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="inline-block w-full px-3 py-2 rounded-xl text-sm break-words shadow-sm transition duration-200 hover:shadow
                                @if($message->sender_id === auth()->id())
                                    bg-primary text-white hover:bg-primary-dark
                                @else
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-primary/20 dark:border-gray-700
                                @endif
                            ">
                                {{ $message->body }}
                            </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500">
                            No messages yet. Say hello!
                        </p>
                    @endforelse
                </div>

                <form wire:submit.prevent="sendMessage" class="border-t border-primary/20 dark:border-gray-700 px-4 py-3 flex items-center gap-3 bg-white dark:bg-gray-900">
                    <textarea
                        wire:model.defer="messageBody"
                        rows="1"
                        class="flex-1 min-w-0 resize-none text-sm border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:border-primary bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition duration-200"
                        placeholder="Type a message..."
                        @keydown.enter="if (window.innerWidth >= 768) { $event.preventDefault(); $wire.sendMessage(); }"
                    ></textarea>
                    <button
                        type="submit"
                        class="inline-flex items-center px-5 py-2.5 bg-primary border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-secondary focus:bg-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50 transition duration-200 hover:scale-105 active:scale-95"
                    >
                        Send
                    </button>
                </form>
            @endif
        </section>
    </div>
</div>

