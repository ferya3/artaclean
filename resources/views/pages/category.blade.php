@extends('layouts.app')

@section('content')
    <section class="border-b border-ink-100 bg-ink-50">
        <div class="container-page pb-12 pt-4">
            <x-breadcrumbs :items="$breadcrumbs" />

            <div class="grid items-center gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <h1 class="text-3xl font-black tracking-tight text-ink-900 sm:text-4xl">{{ $category->name }}</h1>

                    @if ($category->short_description)
                        <p class="mt-4 max-w-2xl text-base leading-8 text-ink-500">{{ $category->short_description }}</p>
                    @endif

                    @if ($category->children->isNotEmpty())
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach ($category->children as $child)
                                <a href="{{ $child->url() }}" class="chip hover:border-ink-900">{{ $child->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($category->image)
                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}"
                         class="hidden aspect-4/3 w-full rounded-[var(--radius-card)] object-cover lg:block">
                @endif
            </div>
        </div>
    </section>

    <div class="container-page py-12">
        <livewire:product-catalog :category="$category->slug" />
    </div>

    @if ($category->description)
        <section class="border-t border-ink-100 bg-ink-50 py-14">
            <div class="container-page max-w-4xl">
                <div class="prose-article rounded-[var(--radius-card)] border border-ink-100 bg-white p-8">
                    {!! $category->description !!}
                </div>
            </div>
        </section>
    @endif

    @if ($category->faqs->isNotEmpty())
        <section class="section pt-14">
            <div class="container-page max-w-3xl">
                <x-faq-list :faqs="$category->faqs" :title="__('ui.faq_title')" />
            </div>
        </section>
    @endif
@endsection
