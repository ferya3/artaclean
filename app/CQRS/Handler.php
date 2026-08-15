<?php

declare(strict_types=1);

namespace App\CQRS;

interface Handler
{
    public function handle(Command|Query $message): mixed;
}
