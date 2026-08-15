<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\ComparisonService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CompareButton extends Component
{
    public int $productId;

    public bool $compact = false;

    public function toggle(ComparisonService $comparison): void
    {
        if (! $comparison->toggle($this->productId) && ! $comparison->has($this->productId)) {
            $this->dispatch('toast', message: __('compare.limit_reached', ['max' => ComparisonService::MAX_ITEMS]));

            return;
        }

        $this->dispatch('comparison-updated');
    }

    #[On('comparison-updated')]
    public function refreshState(): void
    {
        //
    }

    public function render(ComparisonService $comparison): View
    {
        return view('livewire.compare-button', [
            'active' => $comparison->has($this->productId),
        ]);
    }
}
