<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;

/**
 * Translatable columns are stored as `{"fa": "...", "en": "..."}`, so an editor
 * needs one field per locale. This builds the fa/en tab pair for a given
 * attribute instead of repeating the same six lines in every resource.
 */
class TranslatableFields
{
    public static function tabs(string $name, string $label, string $type = 'text', bool $requiredDefault = true): Tabs
    {
        $tabs = [];

        foreach (config('site.locales') as $locale => $meta) {
            $field = self::field($type, "{$name}.{$locale}")
                ->label($label)
                ->required($requiredDefault && $locale === config('app.fallback_locale'));

            $tabs[] = Tabs\Tab::make($meta['native'])->schema([$field]);
        }

        return Tabs::make($name)->tabs($tabs)->columnSpanFull();
    }

    /**
     * A single-line field for one locale — handy when a resource wants the
     * Persian and English inputs side by side rather than tabbed.
     */
    public static function field(string $type, string $statePath): Field
    {
        return match ($type) {
            'textarea' => Textarea::make($statePath)->rows(3),
            'rich' => RichEditor::make($statePath)
                ->toolbarButtons(['bold', 'italic', 'link', 'h2', 'h3', 'bulletList', 'orderedList', 'blockquote', 'table']),
            'tags' => TagsInput::make($statePath),
            default => TextInput::make($statePath)->maxLength(255),
        };
    }
}
