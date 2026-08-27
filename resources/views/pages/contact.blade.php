@extends('layouts.app')

@section('content')
    @include('partials.page-header', [
        'title' => __('contact.title'),
        'subtitle' => __('contact.subtitle'),
        'breadcrumbs' => [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.contact'), 'url' => route('contact')],
        ],
    ])

    <div class="container-page grid gap-10 py-12 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <livewire:contact-form :download-id="request()->integer('download') ?: null" />
        </div>

        <aside class="space-y-4">
            <a href="tel:{{ config('site.phone_e164') }}" class="card-hover flex items-center gap-3 p-5">
                <span class="grid size-11 place-items-center rounded-lg bg-brand-50 text-brand-600">
                    <x-ui-icon name="phone" class="size-5" />
                </span>
                <span>
                    <span class="block text-xs text-ink-400">{{ __('contact.phone_label') }}</span>
                    <span class="tabular block text-sm font-bold text-ink-900">{{ config('site.phone') }}</span>
                </span>
            </a>

            <a href="mailto:{{ config('site.email') }}" class="card-hover flex items-center gap-3 p-5">
                <span class="grid size-11 place-items-center rounded-lg bg-brand-50 text-brand-600">
                    <x-ui-icon name="mail" class="size-5" />
                </span>
                <span class="min-w-0">
                    <span class="block text-xs text-ink-400">{{ __('contact.email_label') }}</span>
                    <span class="block truncate text-sm font-bold text-ink-900">{{ config('site.email') }}</span>
                </span>
            </a>

            <div class="card flex items-start gap-3 p-5">
                <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                    <x-ui-icon name="map-pin" class="size-5" />
                </span>
                <span>
                    <span class="block text-xs text-ink-400">{{ __('contact.address_label') }}</span>
                    <span class="block text-sm leading-6 font-medium text-ink-900">{{ config('site.address') }}</span>
                </span>
            </div>

            <a href="https://wa.me/{{ config('site.whatsapp') }}" target="_blank" rel="noopener noreferrer"
               class="btn w-full bg-[#25D366] text-white hover:brightness-95">
                <x-ui-icon name="whatsapp" class="size-5" />
                {{ __('ui.whatsapp_chat') }}
            </a>

            {{--
                What happens after the form is sent. A buyer hesitating over a
                phone number wants to know who calls, when, and what they will
                be asked — three lines that cost nothing and answer all of it.
            --}}
            <ol class="card space-y-4 p-5">
                <li class="text-sm font-bold text-ink-900">{{ __('contact.next_title') }}</li>
                @foreach (['one', 'two', 'three'] as $index => $step)
                    <li class="flex gap-3">
                        <span class="tabular mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-brand-50 text-xs font-black text-brand-700">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-sm leading-6 text-ink-600">{{ __('contact.next_steps.'.$step) }}</span>
                    </li>
                @endforeach
            </ol>
        </aside>
    </div>
@endsection
