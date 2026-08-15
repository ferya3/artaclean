<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductVideo extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    protected array $translatable = ['title'];

    protected function casts(): array
    {
        return ['title' => 'array'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Embeds are lazy-loaded behind a poster image so a product page with three
     * videos still hits its LCP budget.
     */
    public function embedUrl(): string
    {
        return match ($this->provider) {
            'youtube' => 'https://www.youtube-nocookie.com/embed/'.$this->providerKey(),
            'aparat' => 'https://www.aparat.com/video/video/embed/videohash/'.$this->providerKey().'/vt/frame',
            default => Storage::url($this->url),
        };
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->thumbnail) {
            return Storage::url($this->thumbnail);
        }

        return $this->provider === 'youtube'
            ? 'https://i.ytimg.com/vi/'.$this->providerKey().'/hqdefault.jpg'
            : null;
    }

    private function providerKey(): string
    {
        if (! str_contains($this->url, '/')) {
            return $this->url;
        }

        if ($this->provider === 'youtube') {
            parse_str((string) parse_url($this->url, PHP_URL_QUERY), $query);

            return $query['v'] ?? basename((string) parse_url($this->url, PHP_URL_PATH));
        }

        return basename((string) parse_url($this->url, PHP_URL_PATH));
    }
}
