@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary text-start text-base font-medium text-primary bg-primary/10 focus:outline-none focus:text-secondary focus:bg-primary/20 focus:border-secondary transition duration-200 ease-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-primary hover:bg-primary/5 hover:border-primary/30 focus:outline-none focus:text-primary focus:bg-primary/5 focus:border-primary/30 transition duration-200 ease-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
