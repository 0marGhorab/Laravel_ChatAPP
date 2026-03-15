<x-app-layout>
    <div class="py-4">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Users
                    </h2>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach (\App\Models\User::orderBy('username')->get() as $user)
                        <div class="px-4 py-3 flex items-center justify-between text-sm">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $user->username }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $user->email }}
                                </div>
                            </div>
                            <div>
                                @if ($user->id !== auth()->id())
                                    <a
                                        href="{{ route('chats.start', $user) }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Start Chat
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">(you)</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

