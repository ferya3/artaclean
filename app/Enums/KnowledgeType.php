<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * A buying guide, a comparison and a finished project answer different
 * questions and are read at different points in a purchase, so the knowledge
 * hub separates them rather than pouring everything into one blog feed.
 */
enum KnowledgeType: string implements HasLabel
{
    case Guide = 'guide';
    case Cleaning = 'cleaning';
    case Comparison = 'comparison';
    case CaseStudy = 'case_study';
    case Video = 'video';
    case Article = 'article';

    public function getLabel(): string
    {
        return __('enums.knowledge_type.'.$this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Guide => 'book',
            self::Cleaning => 'sparkles',
            self::Comparison => 'scale',
            self::CaseStudy => 'building',
            self::Video => 'play',
            self::Article => 'document',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
