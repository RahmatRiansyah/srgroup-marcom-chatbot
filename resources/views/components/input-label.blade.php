@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[#524439]']) }}>
    {{ $value ?? $slot }}
</label>
