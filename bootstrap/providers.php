<?php

use App\Providers\AppServiceProvider;
use App\Providers\BusServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\DealerPanelProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    BusServiceProvider::class,
    HorizonServiceProvider::class,
    RepositoryServiceProvider::class,
    AdminPanelProvider::class,
    DealerPanelProvider::class,
];
