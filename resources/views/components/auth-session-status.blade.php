@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-[#885215]']) }}>
        {{ $status }}
    </div>
@endif
