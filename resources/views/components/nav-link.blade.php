@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#885215] text-sm font-medium leading-5 text-[#1b1c1c] focus:outline-none focus:border-[#885215] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[#524439] hover:text-[#1b1c1c] hover:border-[#885215] focus:outline-none focus:text-[#1b1c1c] focus:border-[#885215] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
