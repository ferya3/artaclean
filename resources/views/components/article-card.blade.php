@props(['article'])

{{--
    An article with no photograph of its own falls back to the drawing of the
    machine it is about. That drawing is a product illustration on a
    transparent ground, so it is contained on a lit panel rather than cropped
    to fill the band the way a photograph is.
--}}
@php $hasPhoto = (bool) $article->cover_image; @endphp

<article class="card-hover tick-frame group flex h-full flex-col overflow-hidden">
    <a href="{{ $article->url() }}"
       class="relative block aspect-16/9 overflow-hidden {{ $hasPhoto ? 'bg-ink-100' : 'bg-linear-to-b from-ink-50 to-white' }}">
        @unless ($hasPhoto)
            <span class="pointer-events-none absolute inset-x-12 bottom-5 h-7 rounded-[50%] bg-ink-900/8 blur-xl"></span>
        @endunless

        <img src="{{ $article->coverUrl() }}"
             alt="{{ $article->title }}"
             loading="lazy"
             decoding="async"
             class="relative size-full transition-transform duration-500 ease-[var(--ease-out-quart)] group-hover:scale-105 {{ $hasPhoto ? 'object-cover' : 'object-contain p-6' }}">

        @if ($hasPhoto)
            {{-- Keeps the category chip legible over a light photograph. --}}
            <span class="absolute inset-x-0 bottom-0 h-16 bg-linear-to-t from-ink-950/35 to-transparent"></span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        @if ($article->category)
            <p class="text-[11px] font-semibold tracking-[0.1em] text-brand-600 uppercase">{{ $article->category->name }}</p>
        @endif

        <h3 class="mt-2 text-base leading-7 font-bold text-ink-900">
            <a href="{{ $article->url() }}" class="transition-colors hover:text-brand-700">{{ $article->title }}</a>
        </h3>

        @if ($article->excerpt)
            <p class="mt-2 line-clamp-3 text-sm leading-6 text-ink-500">{{ $article->excerpt }}</p>
        @endif

        <p class="mt-auto pt-4 text-xs text-ink-400">
            {{ \App\Support\Dates::long($article->published_at) }}
            · {{ __('ui.reading_time', ['minutes' => $article->reading_minutes]) }}
        </p>
    </div>
</article>
