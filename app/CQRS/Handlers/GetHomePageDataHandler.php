<?php

declare(strict_types=1);

namespace App\CQRS\Handlers;

use App\Contracts\Repositories\BlogRepository;
use App\Contracts\Repositories\CategoryRepository;
use App\Contracts\Repositories\ProductRepository;
use App\CQRS\Command;
use App\CQRS\Handler;
use App\CQRS\Query;
use App\Models\Brand;
use App\Models\Environment;
use App\Models\Faq;

class GetHomePageDataHandler implements Handler
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
        private readonly BlogRepository $blogs,
    ) {}

    public function handle(Command|Query $message): array
    {
        return [
            'categories' => $this->categories->forHomeGrid(),
            'featured' => $this->products->featured(8),
            'newest' => $this->products->newest(4),
            'environments' => Environment::query()->active()->ordered()->get(),
            'brands' => Brand::query()->active()->ordered()->get(),
            'articles' => $this->blogs->latest(3),
            'faqs' => Faq::query()->active()->global()->orderBy('sort_order')->limit(6)->get(),
        ];
    }
}
