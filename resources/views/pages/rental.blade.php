@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'eyebrow' => __('nav.rental'),
        'title' => __('seo.rental_title'),
        'subtitle' => __('seo.rental_description'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.rental'), 'url' => route('rental')],
        ],
    ])

    <div class="container-page py-12">
        @if ($products->isEmpty())
            <x-empty-state :title="__('ui.no_results')" :body="__('ui.no_results_hint')"
                           :href="route('contact')" :link-label="__('ui.free_consultation')" />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <div class="flex flex-col">
                        <x-product-card :product="$product" />

                        @if ($product->rentalPlans->isNotEmpty())
                            <ul class="-mt-px rounded-b-[var(--radius-card)] border border-t-0 border-ink-100 bg-ink-50 px-5 py-3 text-xs">
                                @foreach ($product->rentalPlans as $plan)
                                    <li class="flex items-center justify-between py-0.5">
                                        <span class="text-ink-500">{{ $plan->period->getLabel() }}</span>
                                        <span class="tabular font-semibold text-ink-900">
                                            @if ($plan->showsPrice())
                                                {{ number_format((float) $plan->price) }}
                                            @else
                                                {{ __('ui.price_on_request') }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-10">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
