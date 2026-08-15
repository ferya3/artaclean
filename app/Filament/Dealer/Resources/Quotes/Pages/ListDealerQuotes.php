<?php

declare(strict_types=1);

namespace App\Filament\Dealer\Resources\Quotes\Pages;

use App\Filament\Dealer\Resources\Quotes\DealerQuoteResource;
use Filament\Resources\Pages\ListRecords;

class ListDealerQuotes extends ListRecords
{
    protected static string $resource = DealerQuoteResource::class;
}
