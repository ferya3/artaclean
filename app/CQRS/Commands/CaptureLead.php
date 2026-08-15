<?php

declare(strict_types=1);

namespace App\CQRS\Commands;

use App\CQRS\Command;
use App\Enums\LeadSource;

/**
 * Every public form funnels into this one command, so lead scoring,
 * de-duplication and notification live in a single place.
 */
final readonly class CaptureLead implements Command
{
    public function __construct(
        public string $name,
        public string $phone,
        public LeadSource $source,
        public ?string $email = null,
        public ?string $city = null,
        public ?string $company = null,
        public ?string $businessType = null,
        public ?int $productId = null,
        public ?int $environmentId = null,
        public ?string $message = null,
        public array $meta = [],
        public ?string $utmSource = null,
        public ?string $utmMedium = null,
        public ?string $utmCampaign = null,
        public ?string $landingPage = null,
        public ?string $ipAddress = null,
    ) {}
}
