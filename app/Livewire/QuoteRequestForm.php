<?php

declare(strict_types=1);

namespace App\Livewire;

use App\CQRS\Bus\CommandBus;
use App\CQRS\Commands\CaptureLead;
use App\CQRS\Commands\SubmitQuoteRequest;
use App\Enums\LeadSource;
use App\Enums\RentalPeriod;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Replaces the usual "add to cart" with the button this market actually needs:
 * "استعلام قیمت روز". Doubles as the rental enquiry form.
 */
class QuoteRequestForm extends Component
{
    public ?int $productId = null;

    public string $mode = 'purchase';

    public bool $open = false;

    #[Validate('required|string|min:3|max:120')]
    public string $name = '';

    #[Validate('required|string|min:8|max:20')]
    public string $phone = '';

    #[Validate('nullable|email|max:160')]
    public string $email = '';

    #[Validate('nullable|string|max:120')]
    public string $city = '';

    #[Validate('nullable|string|max:160')]
    public string $company = '';

    #[Validate('nullable|string|max:60')]
    public string $businessType = '';

    #[Validate('required|integer|min:1|max:999')]
    public int $quantity = 1;

    #[Validate('nullable|string|max:1000')]
    public string $note = '';

    // --- Rental only ---------------------------------------------------------
    public ?string $rentalPeriod = null;

    #[Validate('nullable|integer|min:1|max:365')]
    public ?int $rentalDuration = null;

    public ?string $rentalStartsAt = null;

    public bool $submitted = false;

    public ?string $reference = null;

    public function mount(?int $productId = null, string $mode = 'purchase'): void
    {
        $this->productId = $productId;
        $this->mode = $mode;

        if ($mode === 'rental') {
            $this->rentalPeriod = RentalPeriod::Monthly->value;
            $this->rentalDuration = 1;
        }
    }

    public function submit(CommandBus $bus): void
    {
        $rules = [
            'name' => 'required|string|min:3|max:120',
            'phone' => ['required', 'string', 'regex:/^0?9\d{9}$|^\+?\d{8,15}$/'],
            'email' => 'nullable|email|max:160',
            'city' => 'nullable|string|max:120',
            'company' => 'nullable|string|max:160',
            'quantity' => 'required|integer|min:1|max:999',
            'note' => 'nullable|string|max:1000',
        ];

        if ($this->mode === 'rental') {
            $rules['rentalPeriod'] = 'required|in:daily,weekly,monthly';
            $rules['rentalDuration'] = 'required|integer|min:1|max:365';
        }

        $this->validate($rules);

        $quote = $bus->dispatch(new SubmitQuoteRequest(
            lead: new CaptureLead(
                name: $this->name,
                phone: $this->phone,
                source: $this->mode === 'rental' ? LeadSource::Rental : LeadSource::Quote,
                email: $this->email ?: null,
                city: $this->city ?: null,
                company: $this->company ?: null,
                businessType: $this->businessType ?: null,
                productId: $this->productId,
                message: $this->note ?: null,
                utmSource: request()->query('utm_source'),
                utmMedium: request()->query('utm_medium'),
                utmCampaign: request()->query('utm_campaign'),
                landingPage: url()->current(),
                ipAddress: request()->ip(),
            ),
            productId: $this->productId,
            quantity: $this->quantity,
            type: $this->mode,
            rentalPeriod: $this->rentalPeriod ? RentalPeriod::from($this->rentalPeriod) : null,
            rentalDuration: $this->rentalDuration,
            rentalStartsAt: $this->rentalStartsAt,
            customerNote: $this->note ?: null,
        ));

        $this->submitted = true;
        $this->reference = $quote->reference;
    }

    public function render(): View
    {
        return view('livewire.quote-request-form', [
            'product' => $this->productId ? Product::find($this->productId) : null,
            'businessTypes' => __('business_types'),
            'rentalPeriods' => RentalPeriod::options(),
        ]);
    }
}
