<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#885215] border border-transparent rounded-md font-semibold text-xs text-[#ffffff] uppercase tracking-widest hover:bg-[#784a15] focus:outline-none focus:ring-2 focus:ring-[#885215] focus:ring-offset-2 focus:ring-offset-[#fbf9f8] transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
