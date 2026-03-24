<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-3d inline-flex items-center justify-center px-5 py-2.5 bg-primary border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:opacity-90 focus:opacity-90 active:opacity-80 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition duration-200 ease-out']) }}>
    {{ $slot }}
</button>
