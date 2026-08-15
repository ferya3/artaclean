<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\ComparisonService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CompareTable extends Component
{
    public bool $differencesOnly = false;

    #[On('comparison-updated')]
    public function refreshTable(): void
    {
        // Re-render is enough; the service reads straight from the session.
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
        $products = $comparison->products();
        $rows = $comparison->table($products);

        return view('livewire.compare-table', [
            'products' => $products,
            'rows' => $this->differencesOnly
                ? array_values(array_filter($rows, fn (array $row) => $row['differs']))
                : $rows,
            'totalRows' => count($rows),
        ]);
    }
}
