<?php

declare(strict_types=1);

namespace App\Livewire;

use App\CQRS\Bus\CommandBus;
use App\CQRS\Commands\CaptureLead;
use App\Enums\LeadSource;
use App\Models\Download;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ContactForm extends Component
{
    /** Set when the visitor arrived here from a gated catalog download. */
    public ?int $downloadId = null;

    #[Validate('required|string|min:3|max:120')]
    public string $name = '';

    #[Validate('required|string|max:20')]
    public string $phone = '';

    #[Validate('nullable|email|max:160')]
    public string $email = '';

    #[Validate('nullable|string|max:120')]
    public string $city = '';

    #[Validate('nullable|string|max:160')]
    public string $company = '';

    #[Validate('nullable|string|max:60')]
    public string $businessType = '';

    #[Validate('required|string|min:5|max:2000')]
    public string $message = '';

    public bool $submitted = false;

    public ?string $unlockedDownloadUrl = null;

    public function mount(?int $downloadId = null): void
    {
        $this->downloadId = $downloadId;

        if ($downloadId) {
            $this->message = __('contact.catalog_request');
        }
    }

    public function submit(CommandBus $bus): void
    {
        $this->validate([
            'name' => 'required|string|min:3|max:120',
            'phone' => ['required', 'string', 'regex:/^0?9\d{9}$|^\+?\d{8,15}$/'],
            'email' => 'nullable|email|max:160',
            'city' => 'nullable|string|max:120',
            'company' => 'nullable|string|max:160',
            'message' => 'required|string|min:5|max:2000',
        ]);

        $bus->dispatch(new CaptureLead(
            name: $this->name,
            phone: $this->phone,
            source: $this->downloadId ? LeadSource::Catalog : LeadSource::Contact,
            email: $this->email ?: null,
            city: $this->city ?: null,
            company: $this->company ?: null,
            businessType: $this->businessType ?: null,
            message: $this->message,
            meta: $this->downloadId ? ['download_id' => $this->downloadId] : [],
            landingPage: url()->current(),
            ipAddress: request()->ip(),
        ));

        // Gated file: unlock it for this session and hand over the link.
        if ($this->downloadId && $download = Download::find($this->downloadId)) {
            session()->put('download.unlocked.'.$download->id, true);
            $this->unlockedDownloadUrl = $download->url();
        }

        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.contact-form', [
            'businessTypes' => __('business_types'),
        ]);
    }
}
