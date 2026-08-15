<?php

declare(strict_types=1);

namespace App\Providers;

use App\CQRS\Bus\CommandBus;
use App\CQRS\Bus\QueryBus;
use App\CQRS\Commands;
use App\CQRS\Handlers;
use App\CQRS\Queries;
use Illuminate\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    private const COMMANDS = [
        Commands\CaptureLead::class => Handlers\CaptureLeadHandler::class,
        Commands\SubmitQuoteRequest::class => Handlers\SubmitQuoteRequestHandler::class,
    ];

    /** @var array<class-string, class-string> */
    private const QUERIES = [
        Queries\GetProductCatalog::class => Handlers\GetProductCatalogHandler::class,
        Queries\GetHomePageData::class => Handlers\GetHomePageDataHandler::class,
    ];

    public function register(): void
    {
        $this->app->singleton(
            CommandBus::class,
            fn ($app) => (new CommandBus($app))->register(self::COMMANDS)
        );

        $this->app->singleton(
            QueryBus::class,
            fn ($app) => (new QueryBus($app))->register(self::QUERIES)
        );
    }
}
