<?php

declare(strict_types=1);

namespace App\CQRS;

/**
 * Marker for a write intent. Commands are plain readonly DTOs — they carry the
 * data, the handler owns the behaviour.
 */
interface Command {}
