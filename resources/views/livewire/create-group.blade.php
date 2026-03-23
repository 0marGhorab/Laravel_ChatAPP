<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="font-display text-2xl text-primary tracking-wide">
            {{ __('Create group') }}
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Name the group and choose who to add. You will be included automatically.') }}
        </p>
    </div>

    <form wire:submit="createGroup" class="space-y-6">
        <div>
            <label for="groupName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('Group name') }}
            </label>
            <input
                id="groupName"
                type="text"
                wire:model="groupName"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary focus:border-primary"
                placeholder="{{ __('e.g. Weekend trip') }}"
                autocomplete="off"
            />
            @error('groupName')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('Group image') }} <span class="font-normal text-gray-500">({{ __('optional') }})</span>
            </span>
            <p class="text-xs text-gray-500 mb-2">{{ __('Shown in the chat list and header for everyone in the group.') }}</p>
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" alt="" class="w-20 h-20 rounded-full object-cover border border-gray-200 dark:border-gray-600 shrink-0" />
                @else
                    <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-600 shrink-0 flex items-center justify-center text-xs text-gray-500 text-center px-1">
                        {{ __('No image') }}
                    </div>
                @endif
                <input
                    type="file"
                    wire:model="photo"
                    accept="image/*"
                    class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-95"
                />
            </div>
            <div wire:loading wire:target="photo" class="mt-1 text-xs text-gray-500">{{ __('Uploading…') }}</div>
            @error('photo')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Add members') }}
            </label>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search by username or email...') }}"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 mb-3"
            />
            <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                @forelse ($users as $user)
                    <label class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-primary/5 dark:hover:bg-gray-800 transition">
                        <input
                            type="checkbox"
                            wire:model="selectedUserIds"
                            value="{{ $user->id }}"
                            class="rounded border-gray-300 text-primary focus:ring-primary shrink-0"
                        />
                        <x-user-avatar :user="$user" class="w-9 h-9" />
                        <span class="text-sm text-gray-900 dark:text-gray-100 truncate">{{ $user->username }}</span>
                        <span class="text-xs text-gray-500 truncate ml-auto max-w-[40%]">{{ $user->email }}</span>
                    </label>
                @empty
                    <p class="px-4 py-6 text-sm text-center text-gray-500">
                        {{ __('No users match your search.') }}
                    </p>
                @endforelse
            </div>
            @error('selectedUserIds')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button
                type="submit"
                class="inline-flex items-center px-5 py-2.5 bg-primary border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:opacity-95 focus:ring-2 focus:ring-primary focus:ring-offset-2 transition"
            >
                {{ __('Create group') }}
            </button>
            <a
                href="{{ route('chats') }}"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary underline"
            >
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>
