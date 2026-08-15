@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.products'),
        'title' => __('seo.products_title'),
        'subtitle' => __('seo.products_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.products'), 'url' => route('products.index')],
        ],
    ])

    <div class="container-page py-12">
        <livewire:product-catalog />
    </div>
@endsection
