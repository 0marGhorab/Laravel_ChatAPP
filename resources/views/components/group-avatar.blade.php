@props(['conversation' => null])

@php
    $title = $conversation?->title ?? 'G';
    $letter = mb_strtoupper(mb_substr((string) $title, 0, 1));
    $src = $conversation?->avatar_url;
@endphp

@if ($src)
    <img
        src="{{ $src }}"
        alt=""
        {{ $attributes->merge(['class' => 'w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600 bg-white shrink-0']) }}
    />
@else
    <div
        {{ $attributes->merge(['class' => 'w-8 h-8 shrink-0 rounded-full bg-white border border-gray-200 dark:border-gray-600 flex items-center justify-center text-primary text-sm font-semibold']) }}
        title="{{ __('Group') }}"
    >
        {{ $letter }}
    </div>
@endif
