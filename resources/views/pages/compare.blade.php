@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'title' => __('ui.compare_page.title'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('ui.compare_page.title'), 'url' => route('compare')],
        ],
    ])

    <div class="container-page py-12">
        <livewire:compare-table />
    </div>
@endsection
