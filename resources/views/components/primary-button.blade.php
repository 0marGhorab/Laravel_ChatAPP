<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-primary border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-secondary focus:bg-secondary active:bg-secondary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition duration-200 ease-out hover:scale-[1.02] active:scale-[0.98]']) }}>
    {{ $slot }}
</button>
