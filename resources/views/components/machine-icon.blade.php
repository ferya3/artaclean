@props(['slug'])

{{--
    A distinct machine silhouette per category.

    The grid previously ran nine visually identical tiles, which gave a buyer
    nothing to aim at — scanning it meant reading nine headings in sequence.
    A recognisable shape per category turns that scan into a glance, which is
    the whole job of a category grid.

    Drawn on a 48-unit grid at a heavier stroke than the UI icon set: these
    render at 28-40px, where the 1.6px UI weight goes spindly and washes out.
--}}
@php
    $glyphs = [
        // Drum vacuum: canister body, hose arcing to a wand.
        'industrial-vacuum-cleaner' => '
            <rect x="8" y="20" width="20" height="20" rx="4"/>
            <path d="M8 26h20"/>
            <circle cx="12" cy="43" r="2.5"/><circle cx="24" cy="43" r="2.5"/>
            <path d="M28 26c8 0 8-8 12-8"/>
            <path d="M40 12v10"/>
        ',
        // Wet & dry: same drum, with a droplet.
        'wet-dry-vacuum' => '
            <rect x="6" y="20" width="20" height="20" rx="4"/>
            <path d="M6 26h20"/>
            <circle cx="10" cy="43" r="2.5"/><circle cx="22" cy="43" r="2.5"/>
            <path d="M38 10c3 4 5 6.5 5 9a5 5 0 0 1-10 0c0-2.5 2-5 5-9Z"/>
        ',
        // Walk-behind scrubber: tank, push handle, deck with squeegee.
        'scrubber-dryer' => '
            <path d="M10 22h22a4 4 0 0 1 4 4v8H10Z"/>
            <path d="M32 22 40 8"/>
            <path d="M36 8h8"/>
            <path d="M8 34h30v4H8Z"/>
            <path d="M6 42h26"/>
            <circle cx="14" cy="43" r="2"/><circle cx="32" cy="43" r="2"/>
        ',
        // Single-disc polisher: motor housing, column, disc seen edge-on.
        'floor-polisher' => '
            <rect x="16" y="26" width="16" height="10" rx="3"/>
            <path d="M32 28 40 10"/><path d="M36 10h8"/>
            <ellipse cx="24" cy="40" rx="17" ry="4"/>
            <path d="M24 36v-2"/>
        ',
        // Sweeper: hopper with a cylindrical brush and side brush.
        'industrial-sweeper' => '
            <path d="M12 18h20a4 4 0 0 1 4 4v10H12Z"/>
            <path d="M36 24 43 14"/>
            <circle cx="16" cy="38" r="6"/>
            <path d="M16 32v-2M10 38h-2M16 44v2M22 38h2"/>
            <circle cx="34" cy="40" r="3.5"/>
        ',
        // Upholstery: armchair with a spray arc.
        'upholstery-cleaner' => '
            <path d="M10 30v-8a4 4 0 0 1 4-4h14a4 4 0 0 1 4 4v8"/>
            <path d="M8 30h26a2 2 0 0 1 2 2v6H6v-6a2 2 0 0 1 2-2Z"/>
            <path d="M9 38v4M33 38v4"/>
            <path d="M38 20c4 0 6-4 6-8"/>
            <path d="M40 12h4"/>
        ',
        // Pressure washer: lance with a fan jet.
        'industrial-pressure-washer' => '
            <rect x="6" y="24" width="16" height="16" rx="3"/>
            <path d="M22 30h6l4-4"/>
            <path d="M32 26h6"/>
            <path d="M40 20l4-4M42 26h6M40 32l4 4"/>
            <path d="M10 24v-4h8v4"/>
        ',
        // Steam: boiler with rising vapour.
        'steam-cleaner' => '
            <rect x="10" y="24" width="18" height="16" rx="4"/>
            <path d="M28 32h6a4 4 0 0 0 4-4v-4"/>
            <path d="M16 18c0-3 4-3 4-6M24 18c0-3 4-3 4-6"/>
            <circle cx="19" cy="32" r="3"/>
        ',
        // Spare parts: brush disc and a gear tooth ring.
        'spare-parts' => '
            <circle cx="19" cy="29" r="11"/>
            <circle cx="19" cy="29" r="4"/>
            <path d="M19 18v-4M30 29h4M19 40v4M8 29H4"/>
            <path d="M34 38h10M39 33v10"/>
        ',
        // Chemicals: jerrican with a handle and a level line.
        'cleaning-chemicals' => '
            <path d="M14 18h16a4 4 0 0 1 4 4v18a4 4 0 0 1-4 4H14a4 4 0 0 1-4-4V22a4 4 0 0 1 4-4Z"/>
            <path d="M18 18v-4h8v4"/>
            <path d="M10 32h24"/>
            <path d="M38 22c4 0 4 8 0 8"/>
        ',
    ];

    // Falls back to a generic machine outline for a category added later.
    $glyph = $glyphs[$slug] ?? '
        <rect x="10" y="20" width="20" height="16" rx="4"/>
        <path d="M30 24 40 12"/><path d="M36 12h8"/>
        <circle cx="15" cy="41" r="2.5"/><circle cx="27" cy="41" r="2.5"/>
    ';
@endphp

<svg {{ $attributes->merge(['class' => 'size-8']) }}
     viewBox="0 0 48 48"
     fill="none"
     stroke="currentColor"
     stroke-width="2.4"
     stroke-linecap="round"
     stroke-linejoin="round"
     aria-hidden="true">
    {!! $glyph !!}
</svg>
