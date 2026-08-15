@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.selector'),
        'title' => __('seo.selector_title'),
        'subtitle' => __('ui.selector_subtitle'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.selector'), 'url' => route('selector')],
        ],
    ])

    <div class="container-page py-12">
        <livewire:machine-selector />
    </div>
@endsection
