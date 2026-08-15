<?php

declare(strict_types=1);

namespace App\CQRS\Handlers;

use App\Contracts\Repositories\ProductRepository;
use App\CQRS\Command;
use App\CQRS\Handler;
use App\CQRS\Queries\GetProductCatalog;
use App\CQRS\Query;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetProductCatalogHandler implements Handler
{
    public function __construct(private readonly ProductRepository $products) {}

    /**
     * @return array{products: LengthAwarePaginator, facets: array}
     */
    public function handle(Command|Query $message): array
    {
        /** @var GetProductCatalog $message */
        return [
            'products' => $this->products->paginateFiltered($message->filters, $message->perPage),
            'facets' => $message->withFacets ? $this->products->facetsFor($message->filters) : [],
        ];
    }
}
