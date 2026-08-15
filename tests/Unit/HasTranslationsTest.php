<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasTranslationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reads_the_active_locale(): void
    {
        $brand = Brand::create([
            'slug' => 'karcher',
            'name' => ['fa' => 'کارچر', 'en' => 'Kärcher'],
        ]);

        app()->setLocale('fa');
        $this->assertSame('کارچر', $brand->name);

        app()->setLocale('en');
        $this->assertSame('Kärcher', $brand->name);
    }

    public function test_it_falls_back_when_a_locale_is_missing(): void
    {
        $brand = Brand::create([
            'slug' => 'comac',
            'name' => ['fa' => 'کوماک'],
        ]);

        app()->setLocale('en');

        $this->assertSame('کوماک', $brand->name);
    }

    public function test_assigning_a_string_only_writes_the_active_locale(): void
    {
        $brand = Brand::create([
            'slug' => 'ipc',
            'name' => ['fa' => 'آی‌پی‌سی', 'en' => 'IPC'],
        ]);

        app()->setLocale('en');
        $brand->name = 'IPC Group';
        $brand->save();

        $this->assertSame([
            'fa' => 'آی‌پی‌سی',
            'en' => 'IPC Group',
        ], $brand->fresh()->getTranslations('name'));
    }

    public function test_the_raw_map_stays_available_for_admin_forms(): void
    {
        $brand = Brand::create([
            'slug' => 'nilfisk',
            'name' => ['fa' => 'نیلفیسک', 'en' => 'Nilfisk'],
        ]);

        // Filament binds its `name.fa` / `name.en` fields to this array.
        $this->assertSame(
            ['fa' => 'نیلفیسک', 'en' => 'Nilfisk'],
            $brand->attributesToArray()['name'],
        );
    }
}
