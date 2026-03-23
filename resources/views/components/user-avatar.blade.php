@props(['user' => null])

@php
    $src = $user ? $user->avatar_url : asset('images/avatar-placeholder.svg');
@endphp

<img
    src="{{ $src }}"
    alt=""
    {{ $attributes->merge(['class' => 'w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600 bg-white shrink-0']) }}
/>
