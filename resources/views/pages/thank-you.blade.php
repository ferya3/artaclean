@extends('layouts.app')

@section('content')
    <div class="container-page max-w-xl py-24 text-center">
        <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-50 text-emerald-600">
            <x-ui-icon name="check-circle" class="size-8" />
        </span>

        <h1 class="mt-6 text-2xl font-black text-ink-900">{{ __('ui.form.success_title') }}</h1>
        <p class="mt-3 text-sm leading-7 text-ink-500">{{ __('ui.form.success_body') }}</p>

        <div class="mt-8 flex justify-center gap-3">
            <a href="{{ route('products.index') }}" class="btn-primary">{{ __('ui.view_products') }}</a>
            <a href="{{ route('home') }}" class="btn-outline">{{ __('nav.home') }}</a>
        </div>
    </div>
@endsection
