<?php

declare(strict_types=1);

namespace App\CQRS\Bus;

use App\CQRS\Command;
use Illuminate\Support\Facades\DB;

class CommandBus extends MessageBus
{
    /**
     * Every command runs inside a transaction, so a handler that writes a lead,
     * a quote and an activity row either lands whole or not at all.
     */
    public function dispatch(Command $command): mixed
    {
        $handler = $this->resolveHandler($command);

        return DB::transaction(fn () => $handler->handle($command));
    }
}
