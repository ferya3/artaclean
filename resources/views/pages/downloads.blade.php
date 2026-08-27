@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.downloads'),
        'title' => __('seo.downloads_title'),
        'subtitle' => __('seo.downloads_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.downloads'), 'url' => route('downloads.index')],
        ],
    ])

    <div class="container-page py-12">
        @if ($downloads->isEmpty())
            <x-empty-state :title="__('ui.no_results')" icon="download" />
        @else
            <div class="space-y-12">
                @foreach ($downloads as $type => $files)
                    <section>
                        <h2 class="mb-5 text-xl font-bold text-ink-900">{{ __('enums.download_type.'.$type) }}</h2>

                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($files as $file)
                                {{--
                                    `min-w-0`: a grid item refuses to shrink
                                    below its min-content width by default, so
                                    a long file name plus the lead chip pushed
                                    the card past the viewport on a phone.
                                --}}
                                <a href="{{ $file->url() }}" class="card-hover flex min-w-0 items-center gap-3 p-4">
                                    <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-ink-50 text-ink-500">
                                        <x-ui-icon name="download" class="size-5" />
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-ink-900">{{ $file->title }}</span>
                                        <span class="tabular block text-xs text-ink-400">
                                            @if ($file->product) {{ $file->product->name }} · @endif
                                            {{ strtoupper($file->language) }}
                                            @if ($file->humanSize()) · {{ $file->humanSize() }} @endif
                                        </span>
                                    </span>

                                    @if ($file->requires_lead)
                                        <span class="chip shrink-0 !text-[10px]">{{ __('admin.field.requires_lead') }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
@endsection
