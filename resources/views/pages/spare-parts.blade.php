@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.spare_parts'),
        'title' => __('seo.parts_title'),
        'subtitle' => __('seo.parts_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.spare_parts'), 'url' => route('spare-parts')],
        ],
    ])

    <div class="container-page py-12">
        <livewire:parts-finder />

        @if ($parts->isNotEmpty())
            <div class="mt-12">
                <x-section-heading :title="__('parts.all_title')" />

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($parts as $part)
                        <x-product-card :product="$part" :show-compare="false" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
