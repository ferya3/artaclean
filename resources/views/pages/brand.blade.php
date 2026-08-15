@extends('layouts.app')

@section('content')
    <section class="border-b border-ink-100 bg-ink-50">
        <div class="container-page pb-12 pt-4">
            <x-breadcrumbs :items="[
                ['title' => __('nav.home'), 'url' => route('home')],
                ['title' => __('nav.brands'), 'url' => route('brands.index')],
                ['title' => $brand->name, 'url' => $brand->url()],
            ]" />

            <div class="flex flex-wrap items-center gap-6">
                @if ($brand->logo)
                    <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}"
                         class="h-14 w-auto rounded-lg bg-white object-contain p-2">
                @endif

                <div>
                    <h1 class="text-3xl font-black tracking-tight text-ink-900">{{ $brand->name }}</h1>
                    @if ($brand->country)
                        <p class="mt-1 text-sm text-ink-500">{{ $brand->country }}</p>
                    @endif
                </div>
            </div>

            @if ($brand->description)
                <p class="mt-5 max-w-3xl text-base leading-8 text-ink-500">{{ $brand->description }}</p>
            @endif
        </div>
    </section>

    <div class="container-page py-12">
        <livewire:product-catalog :brands="[$brand->slug]" />
    </div>
@endsection
