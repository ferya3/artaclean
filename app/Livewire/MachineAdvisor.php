<?php

declare(strict_types=1);

namespace App\Livewire;

use App\CQRS\Bus\CommandBus;
use App\CQRS\Commands\CaptureLead;
use App\Enums\LeadSource;
use App\Enums\SoilType;
use App\Enums\SurfaceType;
use App\Services\ProductAdvisor;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * The advisor, as four questions rather than a form.
 *
 * Each answer advances a step, because a buyer who cannot name the machine
 * they need can still answer "what are you cleaning" — and four small
 * questions get answered where one form with four fields gets abandoned.
 */
class MachineAdvisor extends Component
{
    /** Shown inline on the home page: tighter, and links out to the full page. */
    public bool $compact = false;

    public int $step = 1;

    public ?string $surface = null;

    public ?string $soil = null;

    public ?string $areaBand = null;

    public ?string $frequency = null;

    public bool $rentalOnly = false;

    public ?array $result = null;

    // --- "Have a specialist call" -------------------------------------------
    public bool $showLeadForm = false;

    #[Validate('required|string|min:3|max:120')]
    public string $name = '';

    #[Validate('required|string|min:8|max:20')]
    public string $phone = '';

    #[Validate('nullable|string|max:120')]
    public string $company = '';

    public bool $submitted = false;

    public function mount(bool $compact = false): void
    {
        $this->compact = $compact;
    }

    public function choose(string $field, string $value): void
    {
        // A second click on the same answer clears it, so a mis-tap on a phone
        // is one tap to undo rather than a trip back through the steps.
        $this->{$field} = $this->{$field} === $value ? null : $value;

        if ($this->{$field} !== null && $this->step < 4) {
            $this->step++;
        }

        $this->result = null;
    }

    public function goTo(int $step): void
    {
        $this->step = max(1, min($step, 4));
        $this->result = null;
    }

    public function back(): void
    {
        $this->goTo($this->step - 1);
    }

    public function reset_(): void
    {
        $this->reset(['surface', 'soil', 'areaBand', 'frequency', 'result', 'showLeadForm', 'submitted']);
        $this->step = 1;
    }

    public function find(ProductAdvisor $advisor): void
    {
        $this->result = $advisor->recommend(
            surface: $this->surface,
            soil: $this->soil,
            areaBand: $this->areaBand,
            frequency: $this->frequency,
            rentalOnly: $this->rentalOnly,
        );

        $this->showLeadForm = false;
        $this->submitted = false;
    }

    public function requestCall(CommandBus $bus): void
    {
        $this->validate([
            'name' => 'required|string|min:3|max:120',
            'phone' => 'required|string|min:8|max:20',
            'company' => 'nullable|string|max:120',
        ]);

        $bus->dispatch(new CaptureLead(
            name: $this->name,
            phone: $this->phone,
            source: LeadSource::Calculator,
            company: $this->company ?: null,
            productId: $this->result['best']?->id,
            // The four answers are the whole brief. A consultant reading this
            // knows the job before the call connects.
            meta: [
                'surface' => $this->surface,
                'soil' => $this->soil,
                'area_band' => $this->areaBand,
                'frequency' => $this->frequency,
                'required_productivity' => $this->result['required_productivity'] ?? null,
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
        return view('livewire.machine-advisor', [
            'surfaces' => SurfaceType::cases(),
            'soils' => SoilType::cases(),
            'areaBands' => array_keys(ProductAdvisor::AREA_BANDS),
            'frequencies' => ProductAdvisor::FREQUENCIES,
        ]);
    }
}
