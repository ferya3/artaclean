<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\ComparisonService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * The sticky strip that appears once a visitor has picked a machine to
 * compare, so the shortlist is never more than one click away.
 */
class CompareBar extends Component
{
    #[On('comparison-updated')]
    public function refreshBar(): void
    {
        //
    }

    public function remove(int $productId, ComparisonService $comparison): void
    {
        $comparison->remove($productId);
        $this->dispatch('comparison-updated');
    }

    public function clear(ComparisonService $comparison): void
    {
        $comparison->clear();
        $this->dispatch('comparison-updated');
    }

    public function render(ComparisonService $comparison): View
    {
        return view('livewire.compare-bar', [
            'products' => $comparison->products(),
            'max' => ComparisonService::MAX_ITEMS,
        ]);
    }
}
