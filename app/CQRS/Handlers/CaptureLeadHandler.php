<?php

declare(strict_types=1);

namespace App\CQRS\Handlers;

use App\Contracts\Repositories\LeadRepository;
use App\CQRS\Command;
use App\CQRS\Commands\CaptureLead;
use App\CQRS\Handler;
use App\CQRS\Query;
use App\Jobs\NotifySalesOfNewLead;
use App\Models\Lead;
use App\Services\LeadScoringService;

class CaptureLeadHandler implements Handler
{
    public function __construct(
        private readonly LeadRepository $leads,
        private readonly LeadScoringService $scoring,
    ) {}

    public function handle(Command|Query $message): Lead
    {
        /** @var CaptureLead $message */
        $existing = $this->leads->findRecentOpenByPhone($message->phone);

        $lead = $existing ?? $this->leads->create([
            'name' => $message->name,
            'phone' => $message->phone,
            'email' => $message->email,
            'city' => $message->city,
            'company' => $message->company,
            'business_type' => $message->businessType,
            'product_id' => $message->productId,
            'environment_id' => $message->environmentId,
            'source' => $message->source,
            'message' => $message->message,
            'meta' => $message->meta === [] ? null : $message->meta,
            'utm_source' => $message->utmSource,
            'utm_medium' => $message->utmMedium,
            'utm_campaign' => $message->utmCampaign,
            'landing_page' => $message->landingPage,
            'ip_address' => $message->ipAddress,
        ]);

        if ($existing) {
            // Same buyer, second enquiry: enrich the open lead instead of
            // splitting the conversation across two rows.
            $lead->fill(array_filter([
                'email' => $lead->email ?: $message->email,
                'company' => $lead->company ?: $message->company,
                'city' => $lead->city ?: $message->city,
                'business_type' => $lead->business_type ?: $message->businessType,
                'product_id' => $lead->product_id ?: $message->productId,
            ]));

            $this->leads->logActivity(
                $lead,
                'note',
                __('crm.repeat_enquiry', ['source' => $message->source->getLabel()]),
                ['product_id' => $message->productId]
            );
        }

        $lead->score = $this->scoring->score($lead);
        $lead->estimated_value = $this->scoring->estimateValue($lead);
        $lead->save();

        if (! $existing) {
            NotifySalesOfNewLead::dispatch($lead->id);
        }

        return $lead;
    }
}
