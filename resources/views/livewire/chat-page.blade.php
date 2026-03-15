<div class="h-full">
<div class="flex flex-col md:flex-row h-[calc(100vh-6rem)] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden min-h-0 min-w-0">
        {{-- Conversations sidebar --}}
        <aside class="border-r border-gray-200 dark:border-gray-700 flex flex-col w-full xl:w-96 min-h-0 @if(! $showListOnMobile) hidden md:flex @endif">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Chats
                </h2>
                <input
                    type="text"
                    wire:model.debounce.300ms="search"
                    placeholder="Find Chat..."
                    class="flex-1 px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <a
                    href="{{ route('users.index') }}"
                    wire:navigate
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
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
                        class="w-full text-left px-4 py-3 flex flex-col gap-1 border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 @if($isActive) bg-gray-100 dark:bg-gray-800 @endif"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
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

        {{-- Messages panel: w-0 + flex-1 + min-w-0 = width is only from flex, content cannot stretch section --}}
        <section class="w-0 flex-1 min-w-0 flex flex-col min-h-0 overflow-hidden @if($showListOnMobile) hidden md:flex @endif">
            @if (! $activeConversation)
                <div class="flex-1 flex items-center justify-center text-gray-500 text-sm">
                    Select a chat from the left to start messaging.
                </div>
            @else
                <header class="px-2 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            @php
                                $activeOtherNames = $activeConversation->users
                                    ->where('id', '!=', auth()->id())
                                    ->pluck('username')
                                    ->implode(', ');
                            @endphp
                            {{ $activeConversation->title ?? ($activeOtherNames !== '' ? $activeOtherNames : 'You') }}
                        </h2>
                    </div>
                    <button
                        type="button"
                        wire:click="showList"
                        class="inline-flex md:hidden items-center px-3 py-1.5 rounded-full border border-gray-300 dark:border-gray-600 text-xs text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        ← Chats
                    </button>
                </header>

                <div
                    class="flex-1 overflow-y-auto px-2 py-4 space-y-3 bg-gray-50 dark:bg-gray-900"
                    wire:poll.2s="$refresh"
                >
                    @forelse ($activeConversation->messages()->with('sender')->latest()->take(100)->get()->reverse() as $message)
                        <div
                            class="flex flex-col @if($message->sender_id === auth()->id()) items-end @else items-start @endif"
                            @if($loop->last)
                                x-data
                                x-init="$nextTick(() => { $el.scrollIntoView({ behavior: 'smooth', block: 'end' }); })"
                            @endif
                        >
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $message->sender_id === auth()->id() ? 'You' : ($message->sender->username ?? 'User') }}
                                </span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $message->created_at->format('g:i A') }}</span>
                            </div>
                            <div class="inline-block max-w-full md:max-w-[45%] px-3 py-2 rounded-lg text-sm break-words
                                @if($message->sender_id === auth()->id())
                                    bg-indigo-600 text-white
                                @else
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700
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

                <form wire:submit.prevent="sendMessage" class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center gap-3 bg-white dark:bg-gray-900">
                    <textarea
                        wire:model.defer="messageBody"
                        rows="1"
                        class="flex-1 resize-none text-sm border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                        placeholder="Type a message..."
                        @keydown.enter="if (window.innerWidth >= 768) { $event.preventDefault(); $wire.sendMessage(); }"
                    ></textarea>
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition"
                    >
                        Send
                    </button>
                </form>
            @endif
        </section>
    </div>
</div>

