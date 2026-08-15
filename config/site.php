<?php

declare(strict_types=1);

return [

    /*
    |---------------------------------------------------------------------------
    | Storefront contact details
    |---------------------------------------------------------------------------
    |
    | Rendered in the header, footer, WhatsApp widget and the Organization
    | JSON-LD block, so they live in one place instead of being sprinkled
    | through Blade templates.
    |
    */

    'phone' => env('SITE_PHONE', '۰۲۱-۹۱۰۰۰۰۰۰'),
    'phone_e164' => env('SITE_PHONE_E164', '+982191000000'),
    'whatsapp' => env('SITE_WHATSAPP', '989120000000'),
    'email' => env('SITE_EMAIL', 'info@artaclean.ir'),
    'address' => env('SITE_ADDRESS', 'تهران، خیابان آزادی، پلاک ۱'),
    'instagram' => env('SITE_INSTAGRAM', 'https://instagram.com/artaclean'),
    'linkedin' => env('SITE_LINKEDIN'),
    'aparat' => env('SITE_APARAT'),

    /*
    |---------------------------------------------------------------------------
    | Pricing policy
    |---------------------------------------------------------------------------
    |
    | Iranian market prices move week to week, so the storefront hides numbers
    | behind a "استعلام قیمت روز" call to action by default. Flip this to true
    | to show the stored price wherever a product allows it.
    |
    */

    'prices_public' => (bool) env('SITE_PRICES_PUBLIC', false),

    /*
    |---------------------------------------------------------------------------
    | Locales
    |---------------------------------------------------------------------------
    */

    'locales' => [
        'fa' => ['name' => 'فارسی', 'native' => 'فارسی', 'dir' => 'rtl', 'hreflang' => 'fa-IR'],
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr', 'hreflang' => 'en'],
    ],

    /*
    |---------------------------------------------------------------------------
    | Cache TTLs (seconds)
    |---------------------------------------------------------------------------
    */

    'cache' => [
        'navigation' => 3600,
        'home' => 900,
        'product' => 1800,
        'facets' => 900,
        'sitemap' => 3600,
    ],
];
