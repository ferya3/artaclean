@props(['slug'])

{{--
    One silhouette per site type. Without a photograph the environment tiles
    were flat dark rectangles that a visitor had to read to tell apart; a
    building shape identifies each one before the label is read.
--}}
@php
    $glyphs = [
        // Factory: saw-tooth roof and a stack.
        'factory' => '
            <path d="M4 42V24l10 6V24l10 6V24l10 6v12Z"/>
            <path d="M38 42V12h6v30"/>
            <path d="M11 36h4M21 36h4M31 36h4"/>
        ',
        // Warehouse: wide shed with racking bays.
        'warehouse' => '
            <path d="M4 42V20L24 8l20 12v22Z"/>
            <path d="M14 42V28h20v14"/>
            <path d="M14 35h20"/>
        ',
        // Hospital: block with a cross.
        'hospital' => '
            <path d="M8 42V14h32v28Z"/>
            <path d="M24 22v12M18 28h12"/>
            <path d="M4 42h40"/>
        ',
        // Shopping mall: awning over a storefront.
        'shopping-mall' => '
            <path d="M6 20h36l-3-8H9Z"/>
            <path d="M8 20v22h32V20"/>
            <path d="M20 42V30h8v12"/>
        ',
        // Hotel: tower with windows and a canopy.
        'hotel' => '
            <path d="M10 42V10h28v32Z"/>
            <path d="M17 18h4M27 18h4M17 26h4M27 26h4"/>
            <path d="M20 42V34h8v8"/>
            <path d="M4 42h40"/>
        ',
        // Car park: deck levels with a P.
        'parking' => '
            <path d="M8 42V10h32v32Z"/>
            <path d="M8 22h32M8 32h32"/>
            <path d="M20 12v8h5a2.5 2.5 0 0 0 0-5h-5" transform="translate(0 2)"/>
        ',
        // Airport: aircraft over a runway.
        'airport' => '
            <path d="M6 26l8-2 8 8 8-2-6-10 4-2 10 9 6-2a3 3 0 0 1 0 6l-30 8Z"/>
            <path d="M6 42h36"/>
        ',
        // Car wash: vehicle under a spray arch.
        'car-wash' => '
            <path d="M10 36h28"/>
            <path d="M14 36v-6l4-6h12l4 6v6"/>
            <circle cx="18" cy="38" r="2.5"/><circle cx="30" cy="38" r="2.5"/>
            <path d="M8 32V16a4 4 0 0 1 4-4h24a4 4 0 0 1 4 4v16"/>
            <path d="M16 16v3M24 14v3M32 16v3"/>
        ',
    ];

    $glyph = $glyphs[$slug] ?? '
        <path d="M6 42V18L24 8l18 10v24Z"/>
        <path d="M18 42V28h12v14"/>
    ';
@endphp

<svg {{ $attributes->merge(['class' => 'size-10']) }}
     viewBox="0 0 48 48"
     fill="none"
     stroke="currentColor"
     stroke-width="2.2"
     stroke-linecap="round"
     stroke-linejoin="round"
     aria-hidden="true">
    {!! $glyph !!}
</svg>
