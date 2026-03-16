<div class="h-full">
<div class="flex flex-col lg:flex-row h-[calc(100vh-6rem)] bg-white dark:bg-gray-900 border border-primary/20 dark:border-gray-700 rounded-xl overflow-hidden min-h-0 min-w-0 shadow-lg">
        {{-- Conversations sidebar: below lg = toggle with section; lg+ = side by side --}}
        <aside class="border-r border-primary/20 dark:border-gray-700 flex flex-col w-full lg:w-80 lg:shrink-0 xl:w-96 min-h-0 @if(! $showListOnMobile) hidden lg:flex @endif">
            <div class="px-4 py-3 border-b border-primary/20 dark:border-gray-700 flex items-center gap-3">
                <h2 class="font-display text-xl tracking-wide text-primary">
                    Chats
                </h2>
                <input
                    type="text"
                    wire:model.debounce.300ms="search"
                    placeholder="Find Chat..."
                    class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition duration-200"
                />
                <a
                    href="{{ route('users.index') }}"
                    wire:navigate
                    class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-primary/30 text-primary hover:bg-primary/10 focus:outline-none focus:ring-2 focus:ring-primary transition duration-200"
                    title="Search users"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="8.5" cy="7" r="3.5" />
                        <path d="M20 8v6" />
                        <path d="M23 11h-6" />
                    </svg>
                </a>
            </div>

            <div class="flex-1 overflow-y-auto">
                @forelse ($conversations as $conversation)
                    @php
                        $last = $conversation->latestMessage;
                        $isActive = $activeConversation && $activeConversation->id === $conversation->id;
                    @endphp
                    <button
                        wire:click="selectConversation({{ $conversation->id }})"
                        class="w-full text-left px-4 py-3 flex flex-col gap-1 border-b border-gray-100 dark:border-gray-800 hover:bg-primary/5 dark:hover:bg-gray-800 transition duration-200 @if($isActive) bg-primary/10 dark:bg-gray-800 border-l-2 border-l-primary @endif"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100 @if($isActive) text-primary @endif">
                                @php
                                    $otherNames = $conversation->users
                                        ->where('id', '!=', auth()->id())
                                        ->pluck('username')
                                        ->implode(', ');
                                @endphp
                                {{ $conversation->title ?? ($otherNames !== '' ? $otherNames : 'You') }}
                            </span>
                            @if ($last)
                                <span class="text-[11px] text-gray-500">
                                    {{ $last->created_at->shortAbsoluteDiffForHumans() }}
                                </span>
                            @endif
                        </div>
                        @if ($last)
                            <p class="text-xs text-gray-600 dark:text-gray-300 truncate">
                                <span class="font-semibold">{{ $last->sender->username ?? 'You' }}:</span>
                                {{ $last->body }}
                            </p>
                        @else
                            <p class="text-xs text-gray-500">No messages yet</p>
                        @endif
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
                <header class="px-2 py-3 border-b border-primary/20 dark:border-gray-700 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg tracking-wide text-primary">
                            @php
                                $activeOtherNames = $activeConversation->users
                                    ->where('id', '!=', auth()->id())
                                    ->pluck('username')
                                    ->implode(', ');
                            @endphp
                            {{ $activeConversation->title ?? ($activeOtherNames !== '' ? $activeOtherNames : 'You') }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-2" x-data="{ chatMenuOpen: false, chatConfirm: false }" @click.outside="chatMenuOpen = false; chatConfirm = false">
                        <div class="relative hidden lg:block">
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
                                class="absolute right-0 mt-2 w-44 rounded-lg shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 z-20"
                            >
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
                    wire:poll.2s="$refresh"
                >
                    @forelse ($activeConversation->messages()->with('sender')->latest()->take(100)->get()->reverse() as $message)
                        <div
                            class="flex flex-col animate-fade-in @if($message->sender_id === auth()->id()) items-end @else items-start @endif"
                            @if($loop->last)
                                x-data
                                x-init="$nextTick(() => { $el.scrollIntoView({ behavior: 'smooth', block: 'end' }); })"
                            @endif
                        >
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
                            <div class="inline-block max-w-full md:max-w-[45%] px-3 py-2 rounded-xl text-sm break-words shadow-sm transition duration-200 hover:shadow
                                @if($message->sender_id === auth()->id())
                                    bg-primary text-white hover:bg-primary-dark
                                @else
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-primary/20 dark:border-gray-700
                                @endif
                            ">
                                {{ $message->body }}
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

