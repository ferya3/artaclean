<?php

declare(strict_types=1);

namespace App\Filament\Dealer\Resources\Leads\Pages;

use App\Filament\Dealer\Resources\Leads\DealerLeadResource;
use Filament\Resources\Pages\ListRecords;

class ListDealerLeads extends ListRecords
{
    protected static string $resource = DealerLeadResource::class;
}
