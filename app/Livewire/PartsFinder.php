<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * "قطعه را بر اساس مدل دستگاه پیدا کنید."
 *
 * Nobody shops for a squeegee blade by browsing. They have a machine that has
 * stopped working properly and a model number on a plate, and the only
 * question is which part fits it — so the machine is the search box and the
 * parts are the answer.
 */
class PartsFinder extends Component
{
    #[Url(as: 'machine', history: true)]
    public ?string $machine = null;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function updatedSearch(): void
    {
        // Typing a new machine name invalidates the one already chosen.
        $this->machine = null;
    }

    public function select(string $slug): void
    {
        $this->machine = $slug;
        $this->search = '';
    }

    public function clear(): void
    {
        $this->reset(['machine', 'search']);
    }

    /** @return Collection<int, Product> */
    public function machines(): Collection
    {
        if ($this->machine === null && trim($this->search) === '') {
            return collect();
        }

        return Product::query()
            ->active()
            ->with('brand')
            // Only machines that actually have parts recorded: offering a
            // model and then showing nothing is worse than not offering it.
            ->whereHas('compatibleParts')
            ->when($this->search !== '', function (Builder $q) {
                $term = '%'.trim($this->search).'%';

                $q->where(fn (Builder $inner) => $inner
                    ->where('products.name->'.app()->getLocale(), 'like', $term)
                    ->orWhere('products.model_code', 'like', $term)
                    ->orWhere('products.sku', 'like', $term));
            })
            ->limit(8)
            ->get();
    }

    public function render(): View
    {
        $selected = $this->machine
            ? Product::query()->active()->where('slug', $this->machine)->first()
            : null;

        return view('livewire.parts-finder', [
            'matches' => $this->machine ? collect() : $this->machines(),
            'selected' => $selected,
            'parts' => $selected
                ? $selected->compatibleParts()->with(['brand', 'category'])->get()
                : collect(),
        ]);
    }
}
