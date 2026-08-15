<?php

declare(strict_types=1);

namespace App\CQRS;

/**
 * Marker for a read intent. Queries never mutate state, which is what lets the
 * QueryBus cache their results by signature.
 */
interface Query {}
