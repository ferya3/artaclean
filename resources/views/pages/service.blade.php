@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.services'),
        'title' => $service->name,
        'subtitle' => $service->short_description,
        'breadcrumbs' => $breadcrumbs,
    ])

    <div class="container-page grid gap-10 py-12 lg:grid-cols-12 lg:gap-14">
        <div class="lg:col-span-7">
            @if ($service->bulletList())
                <ul class="space-y-4">
                    @foreach ($service->bulletList() as $bullet)
                        <li class="flex items-start gap-3">
                            <x-ui-icon name="check-circle" class="mt-1 size-5 shrink-0 text-brand-600" />
                            <span class="text-base leading-8 text-ink-700">{{ $bullet }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($service->description)
                <div class="prose-article mt-8">{!! $service->description !!}</div>
            @endif

            <div class="mt-10 flex flex-wrap gap-3">
                <a href="{{ route('contact') }}" class="btn-accent btn-lg w-full sm:w-auto">
                    {{ __('services.request') }}
                    <x-ui-icon name="arrow-left" class="size-4 flip-rtl" />
                </a>
                <a href="tel:{{ config('site.phone_e164') }}" class="btn-outline btn-lg w-full sm:w-auto">
                    <x-ui-icon name="phone" class="size-4" />
                    <span class="tabular">{{ config('site.phone') }}</span>
                </a>
            </div>
        </div>

        <aside class="lg:col-span-5">
            <div class="card p-6">
                <p class="text-sm font-bold text-ink-900">{{ __('services.other') }}</p>
                <ul class="mt-4 space-y-1">
                    @foreach ($others as $other)
                        <li>
                            <a href="{{ $other->url() }}"
                               class="flex min-h-12 items-center gap-3 rounded-lg px-3 text-sm text-ink-700 hover:bg-ink-50 hover:text-ink-900">
                                <x-ui-icon :name="$other->icon ?? 'wrench'" class="size-4 shrink-0 text-ink-400" />
                                {{ $other->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>
@endsection
