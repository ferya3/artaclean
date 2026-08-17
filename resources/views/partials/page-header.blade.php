@props(['eyebrow' => null, 'title', 'subtitle' => null, 'breadcrumbs' => []])

{{--
    The banner every inner page opens with.

    It keeps the light ground the house style is built on — the dark panels are
    rationed to the hero, the selector and the footer, and a site where every
    page opens dark spends that contrast on nothing. What it borrows from the
    hero instead is the drawing-sheet vocabulary the rest of the page furniture
    already uses: the ink blueprint grid for scale, a brand key light warming
    the corner the type runs away from, and a hairline that fades out at both
    ends rather than a flat rule running wall to wall.

    `blueprint-ink` and `rule-fade` were both written for a light panel like
    this one and had never been used by anything.

    The title steps up at `lg:` now that the pinned bar sits above it: with the
    banner no longer jammed against the top edge of the window there is room
    for the page name to carry the screen the way the hero's does.
--}}
<section class="relative isolate overflow-hidden bg-ink-50">
    <div class="absolute inset-0 -z-10 blueprint-ink opacity-70"></div>
    <div class="absolute -top-40 -end-24 -z-10 size-[28rem] glow-brand-soft opacity-40"></div>

    <div class="container-page pt-5 pb-12 sm:pt-6 sm:pb-14">
        <x-breadcrumbs :items="$breadcrumbs" />

        @if ($eyebrow)
            <p class="eyebrow mb-3">{{ $eyebrow }}</p>
        @endif

        <h1 class="max-w-3xl text-3xl leading-tight font-black tracking-tight text-ink-900 sm:text-4xl lg:text-[2.75rem] lg:leading-[1.15]">
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="mt-4 max-w-2xl text-base leading-8 text-ink-500 sm:text-lg sm:leading-9">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="rule-fade absolute inset-x-0 bottom-0"></div>
</section>
