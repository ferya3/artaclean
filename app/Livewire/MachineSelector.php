<?php

declare(strict_types=1);

namespace App\Livewire;

use App\CQRS\Bus\CommandBus;
use App\CQRS\Commands\CaptureLead;
use App\Enums\LeadSource;
use App\Models\Environment;
use App\Services\MachineSelectorService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * "محاسبه‌گر انتخاب دستگاه". Step one sizes the machine and shows the result
 * immediately — no form wall. Step two offers to have a consultant call, which
 * is where the lead is captured.
 */
class MachineSelector extends Component
{
    #[Validate('required|integer|min:10|max:500000')]
    public ?int $area = null;

    #[Validate('required|integer|min:1|max:24')]
    public int $shiftHours = 8;

    #[Validate('nullable|exists:environments,slug')]
    public ?string $environment = null;

    public bool $rentalOnly = false;

    public ?array $result = null;

    // --- Consultation form ---------------------------------------------------
    public bool $showLeadForm = false;

    #[Validate('required|string|min:3|max:120')]
    public string $name = '';

    #[Validate('required|string|min:8|max:20')]
    public string $phone = '';

    #[Validate('nullable|string|max:120')]
    public string $city = '';

    #[Validate('nullable|string|max:120')]
    public string $company = '';

    public bool $submitted = false;

    public function calculate(MachineSelectorService $selector): void
    {
        $this->validateOnly('area');
        $this->validateOnly('shiftHours');

        $environment = $this->environment
            ? Environment::query()->where('slug', $this->environment)->first()
            : null;

        $this->result = $selector->recommend(
            area: (int) $this->area,
            shiftHours: $this->shiftHours,
            environment: $environment,
            rentalOnly: $this->rentalOnly,
        );

        $this->showLeadForm = false;
        $this->submitted = false;
    }

    public function requestConsultation(CommandBus $bus): void
    {
        $this->validate([
            'name' => 'required|string|min:3|max:120',
            'phone' => 'required|string|min:8|max:20',
            'city' => 'nullable|string|max:120',
            'company' => 'nullable|string|max:120',
        ]);

        $bus->dispatch(new CaptureLead(
            name: $this->name,
            phone: $this->phone,
            source: LeadSource::Calculator,
            city: $this->city ?: null,
            company: $this->company ?: null,
            businessType: $this->environment,
            productId: $this->result['recommendations']->first()?->id,
            environmentId: $this->environment
                ? Environment::where('slug', $this->environment)->value('id')
                : null,
            // The sizing inputs are the most useful thing a consultant can
            // have in front of them before the first call.
            meta: [
                'area' => $this->area,
                'shift_hours' => $this->shiftHours,
                'required_productivity' => $this->result['required_productivity'] ?? null,
                'machine_class' => $this->result['class']['key'] ?? null,
                'rental' => $this->rentalOnly,
            ],
            landingPage: url()->previous(),
            ipAddress: request()->ip(),
        ));

        $this->submitted = true;
        $this->showLeadForm = false;
    }

    public function render(): View
    {
        return view('livewire.machine-selector', [
            'environments' => Environment::query()->active()->ordered()->get(),
        ]);
    }
}
