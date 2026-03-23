<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $username = '';

    public string $email = '';

    public $photo = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->username = (string) ($user->username ?? '');
        $this->email = (string) ($user->email ?? '');
    }

    /**
     * Persist a cropped photo right after Livewire finishes the temp upload (JS calls this).
     * Ensures the file is stored and other users see the new avatar without a separate Save click.
     */
    public function saveProfilePhotoFromUpload(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || ! $this->photo) {
            return;
        }

        $this->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        $path = $this->photo->store('profile-photos', 'public');

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->forceFill(['avatar_path' => $path])->save();

        $this->photo = null;

        Auth::setUser($user->fresh());

        $this->dispatch('profile-updated', name: $user->username);
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $validated = $this->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class, 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->photo) {
            $path = $this->photo->store('profile-photos', 'public');
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $path;
        }

        $user->fill([
            'username' => $validated['username'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->photo = null;

        Auth::setUser($user->fresh());

        $this->dispatch('profile-updated', name: $user->username);
    }

    public function removeProfilePhoto(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->avatar_path) {
            return;
        }

        Storage::disk('public')->delete($user->avatar_path);
        $user->forceFill(['avatar_path' => null])->save();

        Auth::setUser($user->fresh());

        $this->dispatch('profile-updated', name: $user->username);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('chats', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section id="update-profile-information-form">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="shrink-0" wire:key="profile-avatar-preview">
                @if ($photo)
                    <img
                        src="{{ $photo->temporaryUrl() }}"
                        alt=""
                        class="!w-20 !h-20 rounded-full object-cover ring-2 ring-primary/20 border border-gray-200 dark:border-gray-600 bg-white"
                    />
                @else
                    <x-user-avatar :user="auth()->user()" class="!w-20 !h-20 ring-2 ring-primary/20" />
                @endif
            </div>
            <div class="flex-1 space-y-2">
                <x-input-label for="profile-photo-file-input" :value="__('Profile photo')" />
                <p class="text-xs text-gray-500 mb-1">
                    {{ __('Choose an image, adjust the crop, then Use photo — your avatar is saved to your account immediately.') }}
                </p>
                <input
                    id="profile-photo-file-input"
                    type="file"
                    accept="image/*"
                    class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-95"
                />
                <div wire:loading wire:target="photo,saveProfilePhotoFromUpload" class="text-xs text-gray-500">{{ __('Uploading and saving…') }}</div>
                @error('photo')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                @if (auth()->user()->avatar_path)
                    <button
                        type="button"
                        wire:click="removeProfilePhoto"
                        wire:confirm="{{ __('Remove your profile photo?') }}"
                        class="text-sm text-red-600 hover:underline"
                    >
                        {{ __('Remove photo') }}
                    </button>
                @endif
            </div>
        </div>

        <div
            id="profile-photo-crop-modal"
            wire:ignore
            class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4"
            aria-hidden="true"
            role="dialog"
            aria-modal="true"
            aria-labelledby="profile-crop-title"
        >
            <div
                id="profile-crop-backdrop"
                class="absolute inset-0 bg-black/50"
                tabindex="-1"
            ></div>
            <div class="relative w-full max-w-lg rounded-xl bg-white dark:bg-gray-900 shadow-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                <h3 id="profile-crop-title" class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                    {{ __('Adjust photo') }}
                </h3>
                <p class="text-xs text-gray-500 mb-3">
                    {{ __('Drag to reposition. Use zoom buttons, the slider, or the mouse wheel. The circle shows what will appear as your avatar.') }}
                </p>
                <div class="max-h-[min(50vh,320px)] overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                    <img id="profile-crop-image" class="block max-w-full" alt="" />
                </div>
                <div class="flex items-center gap-3 mt-4">
                    <button
                        type="button"
                        id="profile-crop-zoom-out"
                        class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        title="{{ __('Zoom out') }}"
                    >
                        −
                    </button>
                    <input
                        id="profile-crop-zoom-range"
                        type="range"
                        min="0.1"
                        max="3"
                        step="0.01"
                        value="1"
                        class="flex-1 h-2 accent-primary"
                        title="{{ __('Zoom') }}"
                    />
                    <button
                        type="button"
                        id="profile-crop-zoom-in"
                        class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        title="{{ __('Zoom in') }}"
                    >
                        +
                    </button>
                </div>
                <div class="flex flex-wrap justify-end gap-2 mt-6">
                    <button
                        type="button"
                        id="profile-crop-cancel"
                        class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="button"
                        id="profile-crop-apply"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:opacity-95"
                    >
                        {{ __('Use photo') }}
                    </button>
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input wire:model="username" id="username" name="username" type="text" class="mt-1 block w-full" required autofocus autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
