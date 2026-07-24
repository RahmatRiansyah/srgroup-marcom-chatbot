<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-[#fbf9f8] border border-[#e5e5e1] rounded-md font-semibold text-xs text-[#1b1c1c] uppercase tracking-widest shadow-sm hover:bg-[#f5f3f3] focus:outline-none focus:ring-2 focus:ring-[#885215] focus:ring-offset-2 focus:ring-offset-[#fbf9f8] disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
