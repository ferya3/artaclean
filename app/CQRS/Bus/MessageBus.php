<?php

declare(strict_types=1);

namespace App\CQRS\Bus;

use App\CQRS\Command;
use App\CQRS\Handler;
use App\CQRS\Query;
use App\Providers\BusServiceProvider;
use Illuminate\Contracts\Container\Container;
use RuntimeException;

abstract class MessageBus
{
    /** @var array<class-string, class-string<Handler>> */
    protected array $handlers = [];

    public function __construct(protected Container $container) {}

    /**
     * @param  array<class-string, class-string<Handler>>  $handlers
     */
    public function register(array $handlers): static
    {
        $this->handlers = array_merge($this->handlers, $handlers);

        return $this;
    }

    protected function resolveHandler(Command|Query $message): Handler
    {
        $handler = $this->handlers[$message::class] ?? null;

        if ($handler === null) {
            throw new RuntimeException(sprintf(
                'No handler registered for [%s]. Register it in %s.',
                $message::class,
                BusServiceProvider::class,
            ));
        }

        return $this->container->make($handler);
    }
}
