<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DownloadType: string implements HasLabel
{
    case Catalog = 'catalog';
    case Manual = 'manual';
    case Datasheet = 'datasheet';
    case Certificate = 'certificate';
    case SpareParts = 'spare_parts';
    case Software = 'software';

    public function getLabel(): string
    {
        return __('enums.download_type.'.$this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Catalog => 'book-open',
            self::Manual => 'academic-cap',
            self::Datasheet => 'table-cells',
            self::Certificate => 'shield-check',
            self::SpareParts => 'wrench-screwdriver',
            self::Software => 'cpu-chip',
        };
    }
}
