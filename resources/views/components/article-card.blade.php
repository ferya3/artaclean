@props(['article'])

<article class="card-hover group flex h-full flex-col overflow-hidden">
    <a href="{{ $article->url() }}" class="block aspect-16/9 overflow-hidden bg-ink-100">
        <img src="{{ $article->coverUrl() }}"
             alt="{{ $article->title }}"
             loading="lazy"
             decoding="async"
             class="size-full object-cover transition-transform duration-500 ease-[var(--ease-out-quart)] group-hover:scale-105">
    </a>

    <div class="flex flex-1 flex-col p-5">
        @if ($article->category)
            <p class="text-xs font-medium text-brand-600">{{ $article->category->name }}</p>
        @endif

        <h3 class="mt-1.5 text-base leading-7 font-bold text-ink-900">
            <a href="{{ $article->url() }}" class="hover:text-brand-700">{{ $article->title }}</a>
        </h3>

        @if ($article->excerpt)
            <p class="mt-2 line-clamp-3 text-sm leading-6 text-ink-500">{{ $article->excerpt }}</p>
        @endif

        <p class="mt-auto pt-4 text-xs text-ink-400">
            {{ $article->published_at?->translatedFormat('j F Y') }}
            · {{ __('ui.reading_time', ['minutes' => $article->reading_minutes]) }}
        </p>
    </div>
</article>
