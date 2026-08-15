<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\ProductRepository;
use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Holds the visitor's comparison shortlist in the session and builds the
 * side-by-side table — including which rows actually differ, so the UI can
 * offer a "only show differences" toggle.
 */
class ComparisonService
{
    public const MAX_ITEMS = 4;

    private const SESSION_KEY = 'comparison';

    public function __construct(
        private readonly Session $session,
        private readonly ProductRepository $products,
    ) {}

    /** @return array<int, int> */
    public function ids(): array
    {
        return array_values(array_unique(array_map('intval', (array) $this->session->get(self::SESSION_KEY, []))));
    }

    public function count(): int
    {
        return count($this->ids());
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function isFull(): bool
    {
        return $this->count() >= self::MAX_ITEMS;
    }

    public function add(int $productId): bool
    {
        $ids = $this->ids();

        if (in_array($productId, $ids, true)) {
            return true;
        }

        if (count($ids) >= self::MAX_ITEMS) {
            return false;
        }

        $ids[] = $productId;
        $this->session->put(self::SESSION_KEY, $ids);

        return true;
    }

    public function remove(int $productId): void
    {
        $this->session->put(
            self::SESSION_KEY,
            array_values(array_filter($this->ids(), fn (int $id) => $id !== $productId))
        );
    }

    public function toggle(int $productId): bool
    {
        if ($this->has($productId)) {
            $this->remove($productId);

            return false;
        }

        return $this->add($productId);
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    /** @return Collection<int, Product> */
    public function products(): Collection
    {
        return $this->products->findManyForComparison($this->ids());
    }

    /**
     * One row per spec, one cell per product.
     *
     * @param  Collection<int, Product>  $products
     * @return array<int, array{key: string, label: string, values: array<int, string|null>, differs: bool, best: int|null}>
     */
    public function table(Collection $products): array
    {
        if ($products->isEmpty()) {
            return [];
        }

        $rows = [];

        foreach ($this->comparableColumns() as $key => $definition) {
            $values = $products->map(fn (Product $product) => $product->{$key})->all();

            $rendered = array_map(
                fn ($value) => $this->render($value, $definition['unit'] ?? null),
                $values
            );

            $rows[] = [
                'key' => $key,
                'label' => __('specs.'.$key),
                'values' => $rendered,
                'differs' => count(array_unique($rendered, SORT_REGULAR)) > 1,
                'best' => $this->bestIndex($values, $definition['better'] ?? null),
            ];
        }

        // Free-form specs: union every label across the compared machines so a
        // spec only one of them lists still shows up (blank for the others).
        $labels = $products
            ->flatMap(fn (Product $product) => $product->specs->map(fn ($spec) => $spec->label))
            ->unique()
            ->values();

        foreach ($labels as $label) {
            $rendered = $products->map(function (Product $product) use ($label) {
                $spec = $product->specs->first(fn ($s) => $s->label === $label);

                return $spec?->displayValue();
            })->all();

            $rows[] = [
                'key' => 'spec:'.md5((string) $label),
                'label' => (string) $label,
                'values' => $rendered,
                'differs' => count(array_unique($rendered, SORT_REGULAR)) > 1,
                'best' => null,
            ];
        }

        return $rows;
    }

    /**
     * `better` says which direction wins so the table can flag the leader:
     * more suction is better, less noise is better, weight is a wash.
     */
    private function comparableColumns(): array
    {
        return [
            'model_code' => [],
            'power_watt' => ['unit' => 'W', 'better' => 'high'],
            'voltage' => ['unit' => 'V'],
            'tank_capacity_l' => ['unit' => 'L', 'better' => 'high'],
            'recovery_tank_l' => ['unit' => 'L', 'better' => 'high'],
            'suction_mbar' => ['unit' => 'mbar', 'better' => 'high'],
            'airflow_lpm' => ['unit' => 'L/min', 'better' => 'high'],
            'cleaning_width_mm' => ['unit' => 'mm', 'better' => 'high'],
            'productivity_sqm_h' => ['unit' => 'm²/h', 'better' => 'high'],
            'battery_runtime_min' => ['unit' => 'min', 'better' => 'high'],
            'water_pressure_bar' => ['unit' => 'bar', 'better' => 'high'],
            'steam_temperature_c' => ['unit' => '°C', 'better' => 'high'],
            'noise_db' => ['unit' => 'dB', 'better' => 'low'],
            'weight_kg' => ['unit' => 'kg'],
            'warranty_months' => ['unit' => null, 'better' => 'high'],
        ];
    }

    private function render(mixed $value, ?string $unit): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \BackedEnum) {
            return method_exists($value, 'getLabel') ? $value->getLabel() : $value->value;
        }

        return trim((string) $value.' '.($unit ?? ''));
    }

    private function bestIndex(array $values, ?string $direction): ?int
    {
        if ($direction === null) {
            return null;
        }

        $numeric = array_filter($values, fn ($v) => is_numeric($v));

        if (count($numeric) < 2) {
            return null;
        }

        $target = $direction === 'high' ? max($numeric) : min($numeric);
        $winners = array_keys($values, $target, true);

        // A tie has no winner worth highlighting.
        return count($winners) === 1 ? $winners[0] : null;
    }
}
