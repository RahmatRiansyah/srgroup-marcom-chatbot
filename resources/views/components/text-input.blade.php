@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-[#e5e5e1] bg-[#ffffff] text-[#1b1c1c] focus:border-[#885215] focus:ring-[#885215] rounded-md shadow-sm']) }}>
