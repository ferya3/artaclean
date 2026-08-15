<?php

declare(strict_types=1);

namespace App\Filament\Dealer\Resources\Leads\Pages;

use App\Filament\Dealer\Resources\Leads\DealerLeadResource;
use Filament\Resources\Pages\EditRecord;

class EditDealerLead extends EditRecord
{
    protected static string $resource = DealerLeadResource::class;
}
