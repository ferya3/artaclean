<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\KnowledgeType;
use App\Enums\NavGroup;
use App\Enums\SoilType;
use App\Enums\SurfaceType;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Fills in what the problem-first architecture needs: which family each
 * category belongs to, what each machine actually removes and from what, the
 * services that are half the sale, which parts fit which machines, and the
 * kind of each article.
 *
 * Runs after the catalogue seeders and only writes the new columns, so it is
 * safe on a database that already has products in it.
 */
class SolutionArchitectureSeeder extends Seeder
{
    public function run(): void
    {
        $this->categoryGroups();
        $this->machineCapabilities();
        $this->services();
        $this->compatibility();
        $this->articleTypes();
    }

    /** The four families the mega menu groups by. */
    private function categoryGroups(): void
    {
        $groups = [
            NavGroup::Floor->value => [
                'scrubber-dryer', 'floor-polisher', 'industrial-sweeper', 'industrial-vacuum-cleaner',
            ],
            NavGroup::Pressure->value => [
                'industrial-pressure-washer', 'steam-cleaner',
            ],
            NavGroup::Specialist->value => [
                'upholstery-cleaner', 'wet-dry-vacuum',
            ],
            NavGroup::Consumable->value => [
                'cleaning-chemicals', 'spare-parts',
            ],
        ];

        foreach ($groups as $group => $slugs) {
            Category::whereIn('slug', $slugs)->update(['nav_group' => $group]);
        }
    }

    /**
     * What each machine class removes, and from what.
     *
     * Defaults are set per category — that is how a buyer reasons about it —
     * and then a handful of machines override them where the model genuinely
     * differs, such as the wet/dry vacuum that also picks up liquids.
     */
    private function machineCapabilities(): void
    {
        $byCategory = [
            'scrubber-dryer' => [
                'soil' => [SoilType::Dust, SoilType::Oil, SoilType::Grease, SoilType::Food, SoilType::Liquid],
                'surface' => [SurfaceType::Concrete, SurfaceType::Epoxy, SurfaceType::Tile],
            ],
            'industrial-sweeper' => [
                'soil' => [SoilType::Dust, SoilType::Mud],
                'surface' => [SurfaceType::Concrete, SurfaceType::Epoxy, SurfaceType::Outdoor],
            ],
            'industrial-vacuum-cleaner' => [
                'soil' => [SoilType::Dust, SoilType::Chemical],
                'surface' => [SurfaceType::Concrete, SurfaceType::Machinery, SurfaceType::Tile],
            ],
            'wet-dry-vacuum' => [
                'soil' => [SoilType::Dust, SoilType::Liquid, SoilType::Mud, SoilType::Oil],
                'surface' => [SurfaceType::Concrete, SurfaceType::Machinery, SurfaceType::Tile],
            ],
            'floor-polisher' => [
                'soil' => [SoilType::Dust, SoilType::Grease],
                'surface' => [SurfaceType::Tile, SurfaceType::Concrete, SurfaceType::Carpet],
            ],
            'industrial-pressure-washer' => [
                'soil' => [SoilType::Mud, SoilType::Oil, SoilType::Grease, SoilType::Dust],
                'surface' => [SurfaceType::Outdoor, SurfaceType::Machinery, SurfaceType::Vertical, SurfaceType::Concrete],
            ],
            'steam-cleaner' => [
                'soil' => [SoilType::Grease, SoilType::Food, SoilType::Oil],
                'surface' => [SurfaceType::Tile, SurfaceType::Machinery, SurfaceType::Vertical, SurfaceType::Upholstery],
            ],
            'upholstery-cleaner' => [
                'soil' => [SoilType::Dust, SoilType::Food, SoilType::Liquid],
                'surface' => [SurfaceType::Carpet, SurfaceType::Upholstery],
            ],
            'cleaning-chemicals' => [
                'soil' => [SoilType::Oil, SoilType::Grease, SoilType::Food],
                'surface' => [SurfaceType::Concrete, SurfaceType::Epoxy, SurfaceType::Tile],
            ],
        ];

        foreach ($byCategory as $slug => $capability) {
            $categoryId = Category::where('slug', $slug)->value('id');

            if ($categoryId === null) {
                continue;
            }

            Product::where('category_id', $categoryId)->update([
                'soil_types' => array_map(fn (SoilType $s) => $s->value, $capability['soil']),
                'surface_types' => array_map(fn (SurfaceType $s) => $s->value, $capability['surface']),
            ]);
        }

        // Machines whose own specification departs from the category default.
        $overrides = [
            // The three-phase 80 litre drum is the one rated for wet pickup.
            'arta-vac-80-3ph' => [
                'soil' => [SoilType::Dust, SoilType::Liquid, SoilType::Chemical, SoilType::Oil],
                'surface' => [SurfaceType::Concrete, SurfaceType::Machinery],
            ],
            // A ride-on scrubber is not going anywhere near a carpet, but it
            // is the only machine here rated for an open yard.
            'arta-rider-100' => [
                'soil' => [SoilType::Dust, SoilType::Oil, SoilType::Grease, SoilType::Liquid],
                'surface' => [SurfaceType::Concrete, SurfaceType::Epoxy, SurfaceType::Outdoor],
            ],
        ];

        foreach ($overrides as $slug => $capability) {
            Product::where('slug', $slug)->update([
                'soil_types' => array_map(fn (SoilType $s) => $s->value, $capability['soil']),
                'surface_types' => array_map(fn (SurfaceType $s) => $s->value, $capability['surface']),
            ]);
        }
    }

    /** The seven services the after-sales section is built from. */
    private function services(): void
    {
        $services = [
            [
                'installation', 'wrench',
                'نصب و راه‌اندازی', 'Installation and commissioning',
                'تحویل دستگاه در محل، راه‌اندازی و تست عملکرد با حضور اپراتور شما.',
                'On-site delivery, commissioning and a performance test with your operator present.',
                [
                    'بازدید محل و بررسی مسیر تردد دستگاه پیش از تحویل',
                    'راه‌اندازی، تنظیم فشار برس و تیغه و تست عملکرد',
                    'تحویل کتبی دستگاه همراه با چک‌لیست راه‌اندازی',
                ],
                [
                    'A site visit and a route check before delivery',
                    'Commissioning, brush and squeegee pressure setting, performance test',
                    'A written handover with the commissioning checklist',
                ],
            ],
            [
                'maintenance', 'clock',
                'سرویس دوره‌ای', 'Scheduled maintenance',
                'قرارداد سرویس دوره‌ای بر اساس ساعت کارکرد دستگاه، نه تقویم.',
                'A maintenance contract keyed to running hours, not to the calendar.',
                [
                    'برنامه سرویس بر اساس ساعت کارکرد واقعی دستگاه',
                    'تعویض قطعات مصرفی پیش از خرابی، نه بعد از آن',
                    'گزارش وضعیت دستگاه بعد از هر سرویس',
                ],
                [
                    'A service plan based on actual running hours',
                    'Wear parts replaced before they fail, not after',
                    'A condition report after every visit',
                ],
            ],
            [
                'repair', 'wrench',
                'تعمیرات', 'Repair',
                'تعمیر در محل برای خرابی‌های رایج، و کارگاه مرکزی برای تعمیرات اساسی.',
                'On-site repair for common faults, and a central workshop for overhauls.',
                [
                    'اعزام تکنسین برای خرابی‌های متوقف‌کننده خط کار',
                    'تعمیر موتور مکش، پمپ، شارژر و برد کنترل',
                    'اعلام هزینه پیش از شروع تعمیر',
                ],
                [
                    'A technician dispatched for faults that stop the job',
                    'Suction motors, pumps, chargers and control boards',
                    'A quoted cost before the work starts',
                ],
            ],
            [
                'spare-parts', 'cube',
                'قطعات یدکی', 'Spare parts',
                'موجودی قطعات مصرفی و یدکی برای دستگاه‌هایی که می‌فروشیم.',
                'Stocked wear and spare parts for every machine we sell.',
                [
                    'تیغه، برس، پد، فیلتر، باتری و شارژر',
                    'جستجوی قطعه بر اساس مدل دستگاه',
                    'ارسال به سراسر کشور',
                ],
                [
                    'Squeegees, brushes, pads, filters, batteries and chargers',
                    'Part search by machine model',
                    'Nationwide dispatch',
                ],
            ],
            [
                'training', 'user-group',
                'آموزش اپراتور', 'Operator training',
                'آموزش عملی اپراتور در محل؛ بیشتر خرابی‌ها ریشه در کار اشتباه دارند.',
                'Hands-on operator training on site: most failures start as operator error.',
                [
                    'آموزش کار با دستگاه و تنظیم دُز مواد شوینده',
                    'آموزش نگهداری روزانه: تخلیه، شست‌وشو و شارژ',
                    'جزوه و ویدئوی آموزشی فارسی',
                ],
                [
                    'Operating the machine and setting detergent dosage',
                    'Daily care: dumping, rinsing and charging',
                    'A Persian handbook and training video',
                ],
            ],
            [
                'rental', 'calculator',
                'اجاره دستگاه', 'Equipment rental',
                'اجاره روزانه، هفتگی و ماهانه برای پروژه‌های موقت و دوره‌های اوج کار.',
                'Daily, weekly and monthly rental for temporary projects and peak periods.',
                [
                    'دوره‌های روزانه، هفتگی و ماهانه',
                    'تحویل و جمع‌آوری دستگاه در محل',
                    'امکان تبدیل اجاره به خرید',
                ],
                [
                    'Daily, weekly and monthly terms',
                    'Delivery and collection on site',
                    'Rental convertible to purchase',
                ],
            ],
            [
                'warranty', 'shield',
                'گارانتی', 'Warranty',
                'گارانتی رسمی دستگاه با تعهد زمان پاسخ‌گویی مشخص.',
                'A manufacturer warranty with a committed response time.',
                [
                    'گارانتی ۱۲ تا ۲۴ ماهه بسته به مدل',
                    'تعهد پاسخ‌گویی حداکثر ۴۸ ساعت کاری',
                    'ثبت شماره سریال دستگاه در پرونده مشتری',
                ],
                [
                    '12 to 24 months depending on the model',
                    'A committed response within two working days',
                    'Serial numbers recorded against your account',
                ],
            ],
        ];

        foreach ($services as $index => [$slug, $icon, $nameFa, $nameEn, $shortFa, $shortEn, $bulletsFa, $bulletsEn]) {
            Service::updateOrCreate(['slug' => $slug], [
                'name' => ['fa' => $nameFa, 'en' => $nameEn],
                'short_description' => ['fa' => $shortFa, 'en' => $shortEn],
                'description' => [
                    'fa' => '<p>'.$shortFa.'</p>',
                    'en' => '<p>'.$shortEn.'</p>',
                ],
                'bullets' => ['fa' => $bulletsFa, 'en' => $bulletsEn],
                'icon' => $icon,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }

    /** Which machines the seeded squeegee blade set actually fits. */
    private function compatibility(): void
    {
        $part = Product::where('slug', 'squeegee-blade-set-70')->first();

        if (! $part) {
            return;
        }

        $machines = Product::whereIn('slug', [
            'arta-scrub-70', 'arta-scrub-50b', 'karcher-bd-50-70', 'ipc-ct40', 'arta-rider-100',
        ])->pluck('id');

        $part->fitsMachines()->syncWithoutDetaching($machines);
    }

    /** The seeded articles, sorted into the knowledge hub's shelves. */
    private function articleTypes(): void
    {
        $types = [
            'how-to-choose-industrial-scrubber' => KnowledgeType::Guide,
            'industrial-vacuum-filter-guide' => KnowledgeType::Guide,
            'scrubber-maintenance-checklist' => KnowledgeType::Cleaning,
        ];

        foreach ($types as $slug => $type) {
            Blog::where('slug', $slug)->update(['type' => $type->value]);
        }
    }
}
