@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[#885215] text-start text-base font-medium text-[#1b1c1c] bg-[#fbf9f8] focus:outline-none focus:text-[#1b1c1c] focus:bg-[#f5f3f3] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-[#524439] hover:text-[#1b1c1c] hover:bg-[#fbf9f8] hover:border-[#885215] focus:outline-none focus:text-[#1b1c1c] focus:bg-[#fbf9f8] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
