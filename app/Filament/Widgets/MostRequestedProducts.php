<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Which machines buyers actually ask about, as opposed to which ones get
 * browsed — useful for deciding what to stock and what to feature.
 */
class MostRequestedProducts extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return __('admin.widget.most_requested');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['brand', 'category'])
                    ->where('quotes_count', '>', 0)
                    ->orderByDesc('quotes_count')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('name')->label(__('admin.field.product'))->wrap(),
                TextColumn::make('category.name')->label(__('admin.field.category'))->badge(),
                TextColumn::make('quotes_count')->label(__('admin.field.quotes_count'))->alignEnd()->weight('bold'),
                TextColumn::make('views_count')->label(__('admin.field.views'))->alignEnd(),
                TextColumn::make('conversion')
                    ->label(__('admin.field.enquiry_rate'))
                    ->alignEnd()
                    ->state(fn (Product $record) => $record->views_count > 0
                        ? round($record->quotes_count / $record->views_count * 100, 1).'%'
                        : '—'),
            ])
            ->paginated(false);
    }
}
