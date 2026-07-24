<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#f4e3d6] border border-transparent rounded-md font-semibold text-xs text-[#885215] uppercase tracking-widest hover:bg-[#e7c5a6] active:bg-[#d6b192] focus:outline-none focus:ring-2 focus:ring-[#885215] focus:ring-offset-2 focus:ring-offset-[#fbf9f8] transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
