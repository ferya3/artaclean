<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Download;
use App\Models\Environment;
use App\Models\Faq;
use App\Models\Product;
use App\Models\RentalPlan;
use Illuminate\Database\Seeder;

/**
 * A representative machine range. The technical figures are typical of the
 * classes they represent, so the filters, the comparison table and the sizing
 * calculator all behave realistically out of the box.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');
        $brands = Brand::pluck('id', 'slug');
        $environments = Environment::pluck('id', 'slug');

        foreach ($this->machines() as $index => $machine) {
            $product = Product::updateOrCreate(['slug' => $machine['slug']], [
                'category_id' => $categories[$machine['category']],
                'brand_id' => $brands[$machine['brand']] ?? null,
                'sku' => strtoupper(str_replace('-', '', $machine['slug'])),
                'model_code' => $machine['model'],
                'name' => ['fa' => $machine['name_fa'], 'en' => $machine['name_en']],
                'short_description' => ['fa' => $machine['short_fa'], 'en' => $machine['short_en']],
                'description' => [
                    'fa' => '<p>'.$machine['short_fa'].'</p><p>'.$machine['body_fa'].'</p>',
                    'en' => '<p>'.$machine['short_en'].'</p>',
                ],
                'highlights' => ['fa' => $machine['highlights_fa'], 'en' => $machine['highlights_en'] ?? []],
                'price' => $machine['price'] ?? null,
                'price_visible' => false,
                'power_source' => $machine['power_source'],
                'operator_type' => $machine['operator_type'] ?? null,
                'power_watt' => $machine['power_watt'] ?? null,
                'voltage' => $machine['voltage'] ?? null,
                'tank_capacity_l' => $machine['tank'] ?? null,
                'recovery_tank_l' => $machine['recovery'] ?? null,
                'suction_mbar' => $machine['suction'] ?? null,
                'airflow_lpm' => $machine['airflow'] ?? null,
                'cleaning_width_mm' => $machine['width'] ?? null,
                'productivity_sqm_h' => $machine['productivity'] ?? null,
                'weight_kg' => $machine['weight'] ?? null,
                'noise_db' => $machine['noise'] ?? null,
                'battery_runtime_min' => $machine['runtime'] ?? null,
                'water_pressure_bar' => $machine['pressure'] ?? null,
                'steam_temperature_c' => $machine['steam'] ?? null,
                'warranty_months' => $machine['warranty'] ?? 12,
                'stock_status' => $machine['stock'] ?? 'in_stock',
                'is_active' => true,
                'is_featured' => $machine['featured'] ?? false,
                'is_rentable' => $machine['rentable'] ?? false,
                'sort_order' => $index,
                'seo_title' => [
                    'fa' => 'خرید '.$machine['name_fa'].' | مشخصات فنی و قیمت روز',
                    'en' => $machine['name_en'].' — specifications and pricing',
                ],
                'seo_description' => ['fa' => $machine['short_fa'], 'en' => $machine['short_en']],
            ]);

            $product->environments()->sync(
                collect($machine['environments'])
                    ->mapWithKeys(fn (string $slug) => [
                        $environments[$slug] => ['suitability' => 5],
                    ])
                    ->all()
            );

            $this->specs($product, $machine);
            $this->faqs($product, $machine);

            if ($product->is_rentable) {
                $this->rentalPlans($product);
            }
        }

        $this->downloads();
    }

    private function specs(Product $product, array $machine): void
    {
        $product->specs()->delete();

        foreach ($machine['specs'] ?? [] as $order => [$labelFa, $labelEn, $valueFa, $valueEn]) {
            $product->specs()->create([
                'group' => 'general',
                'label' => ['fa' => $labelFa, 'en' => $labelEn],
                'value' => ['fa' => $valueFa, 'en' => $valueEn],
                'sort_order' => $order,
            ]);
        }
    }

    private function faqs(Product $product, array $machine): void
    {
        $product->faqs()->delete();

        foreach ($machine['faqs'] ?? [] as $order => [$questionFa, $answerFa]) {
            Faq::create([
                'faqable_type' => Product::class,
                'faqable_id' => $product->id,
                'question' => ['fa' => $questionFa],
                'answer' => ['fa' => '<p>'.$answerFa.'</p>'],
                'sort_order' => $order,
                'is_active' => true,
                'in_schema' => true,
            ]);
        }
    }

    private function rentalPlans(Product $product): void
    {
        $base = (int) ($product->price ?? 500_000_000);

        foreach ([
            ['daily', (int) round($base * 0.012, -5), 1],
            ['weekly', (int) round($base * 0.06, -5), 1],
            ['monthly', (int) round($base * 0.18, -5), 1],
        ] as [$period, $price, $min]) {
            RentalPlan::updateOrCreate(
                ['product_id' => $product->id, 'period' => $period],
                [
                    'price' => $price,
                    'deposit' => (int) round($base * 0.2, -5),
                    'min_duration' => $min,
                    'price_visible' => false,
                    'is_active' => true,
                ]
            );
        }
    }

    private function downloads(): void
    {
        // Files are seeded as metadata only; upload the real PDFs through the
        // admin panel and the storefront picks them up immediately.
        $catalog = Download::firstOrNew(['file_path' => 'downloads/artaclean-general-catalog.pdf']);

        $catalog->fill([
            'type' => 'catalog',
            'title' => ['fa' => 'کاتالوگ جامع محصولات آرتاکلین', 'en' => 'ArtaClean general catalog'],
            'description' => [
                'fa' => 'تمام دسته‌بندی‌ها، مشخصات فنی و لوازم جانبی در یک فایل.',
                'en' => 'Every category, specification and accessory in one file.',
            ],
            'mime_type' => 'application/pdf',
            'file_size' => 8_400_000,
            'language' => 'fa',
            'requires_lead' => true,
            'is_public' => true,
            'sort_order' => 0,
        ])->save();

        foreach (Product::query()->where('is_featured', true)->get() as $order => $product) {
            Download::updateOrCreate(
                ['file_path' => 'downloads/datasheet-'.$product->slug.'.pdf'],
                [
                    'product_id' => $product->id,
                    'type' => 'datasheet',
                    'title' => [
                        'fa' => 'دیتاشیت فنی '.$product->translate('name', 'fa'),
                        'en' => $product->translate('name', 'en').' datasheet',
                    ],
                    'mime_type' => 'application/pdf',
                    'file_size' => 640_000,
                    'language' => 'fa',
                    'is_public' => true,
                    'sort_order' => $order,
                ]
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function machines(): array
    {
        return [
            // ---------------------------------------------------- Vacuums ---
            [
                'slug' => 'arta-vac-30', 'category' => 'industrial-vacuum-cleaner', 'brand' => 'artaclean',
                'model' => 'AV-30', 'name_fa' => 'جاروبرقی صنعتی آرتا ۳۰ لیتری', 'name_en' => 'Arta Vac 30L',
                'short_fa' => 'جاروبرقی تک‌فاز ۳۰ لیتری با مخزن استیل، مناسب کارگاه و تعمیرگاه.',
                'short_en' => 'Single-phase 30 litre vacuum with a stainless tank for workshops.',
                'body_fa' => 'موتور بای‌پس با خنک‌کاری مجزا اجازه می‌دهد دستگاه ساعت‌ها بدون افت مکش کار کند. فیلتر پارچه‌ای قابل شستشو هزینه مصرفی را پایین نگه می‌دارد.',
                'highlights_fa' => ['مخزن استیل ضدزنگ', 'فیلتر قابل شستشو', 'چرخ‌های صنعتی ۱۲۵ میلی‌متری'],
                'highlights_en' => ['Stainless steel tank', 'Washable filter', '125 mm industrial castors'],
                'power_source' => 'electric', 'operator_type' => 'handheld',
                'power_watt' => 1400, 'voltage' => 220, 'tank' => 30, 'suction' => 220, 'airflow' => 3600,
                'weight' => 11, 'noise' => 68, 'price' => 78_000_000, 'featured' => true,
                'environments' => ['factory', 'car-wash', 'warehouse'],
                'specs' => [
                    ['طول شلنگ', 'Hose length', '۳ متر', '3 m'],
                    ['قطر شلنگ', 'Hose diameter', '۳۸ میلی‌متر', '38 mm'],
                    ['طول کابل', 'Cable length', '۸ متر', '8 m'],
                ],
                'faqs' => [
                    ['آیا برای مکش مایعات هم مناسب است؟', 'بله، با تعویض فیلتر به حالت آب و خاک، تا سقف مخزن مایعات را مکش می‌کند.'],
                    ['فیلتر هر چند وقت تعویض می‌شود؟', 'فیلتر پارچه‌ای قابل شستشو است و در کاربری معمول سالی یک بار تعویض می‌شود.'],
                ],
            ],
            [
                'slug' => 'arta-vac-80-3ph', 'category' => 'industrial-vacuum-cleaner', 'brand' => 'artaclean',
                'model' => 'AV-80T', 'name_fa' => 'جاروبرقی صنعتی سه‌فاز ۸۰ لیتری', 'name_en' => 'Arta Vac 80L Three-Phase',
                'short_fa' => 'مکنده سه‌فاز با موتور ساید چنل برای کار پیوسته در خط تولید.',
                'short_en' => 'Three-phase side-channel vacuum built for continuous production-line duty.',
                'body_fa' => 'موتور ساید چنل بدون زغال، برای کار ۲۴ ساعته طراحی شده و نیاز به سرویس دوره‌ای کمی دارد.',
                'highlights_fa' => ['موتور ساید چنل بدون زغال', 'کار پیوسته ۲۴ ساعته', 'فیلتر هپا اختیاری'],
                'highlights_en' => ['Brushless side-channel motor', '24-hour continuous duty', 'Optional HEPA filter'],
                'power_source' => 'electric', 'operator_type' => 'stationary',
                'power_watt' => 4000, 'voltage' => 380, 'tank' => 80, 'suction' => 320, 'airflow' => 5400,
                'weight' => 78, 'noise' => 74, 'price' => 420_000_000, 'warranty' => 24,
                'environments' => ['factory', 'warehouse'],
                'specs' => [
                    ['نوع موتور', 'Motor type', 'ساید چنل', 'Side channel'],
                    ['سطح فیلتر', 'Filter area', '۲ متر مربع', '2 m²'],
                ],
                'faqs' => [
                    ['برای گرد و غبار سیمان مناسب است؟', 'بله، با فیلتر هپا H14 و سیستم تکاندن دستی برای غبار ریز طراحی شده است.'],
                ],
            ],
            [
                'slug' => 'nilfisk-attix-50', 'category' => 'wet-dry-vacuum', 'brand' => 'nilfisk',
                'model' => 'ATTIX 50', 'name_fa' => 'جاروبرقی آب و خاک نیلفیسک ATTIX 50', 'name_en' => 'Nilfisk ATTIX 50',
                'short_fa' => 'مکنده آب و خاک ۵۰ لیتری با تکاندن خودکار فیلتر.',
                'short_en' => '50 litre wet & dry vacuum with automatic filter cleaning.',
                'body_fa' => 'سیستم XtremeClean فیلتر را در حین کار تمیز می‌کند و افت مکش را از بین می‌برد.',
                'highlights_fa' => ['تکاندن خودکار فیلتر', 'مخزن ۵۰ لیتری', 'سوکت ابزار برق'],
                'highlights_en' => ['Automatic filter cleaning', '50 litre container', 'Power tool socket'],
                'power_source' => 'electric', 'operator_type' => 'handheld',
                'power_watt' => 1500, 'voltage' => 220, 'tank' => 50, 'suction' => 250, 'airflow' => 3700,
                'weight' => 17, 'noise' => 64, 'price' => 195_000_000, 'featured' => true, 'rentable' => true,
                'environments' => ['factory', 'car-wash', 'parking'],
                'specs' => [
                    ['سیستم تکاندن', 'Filter cleaning', 'XtremeClean خودکار', 'XtremeClean automatic'],
                    ['کلاس غبار', 'Dust class', 'L', 'L'],
                ],
                'faqs' => [
                    ['آیا با ابزار برقی همزمان کار می‌کند؟', 'بله، سوکت ابزار روی دستگاه با روشن شدن ابزار، مکش را خودکار فعال می‌کند.'],
                ],
            ],
            [
                'slug' => 'karcher-nt-75', 'category' => 'industrial-vacuum-cleaner', 'brand' => 'karcher',
                'model' => 'NT 75/2', 'name_fa' => 'جاروبرقی صنعتی کارچر NT 75/2', 'name_en' => 'Kärcher NT 75/2',
                'short_fa' => 'دو موتوره ۷۵ لیتری برای کاربری سنگین و مداوم.',
                'short_en' => 'Twin-motor 75 litre vacuum for heavy continuous use.',
                'body_fa' => 'دو توربین مستقل امکان کار با یک یا هر دو موتور را می‌دهد و مصرف برق را مدیریت می‌کند.',
                'highlights_fa' => ['دو موتور مستقل', 'مخزن ۷۵ لیتری قابل تخلیه', 'فیلتر تخت قابل شستشو'],
                'highlights_en' => ['Two independent motors', '75 litre tipping container', 'Washable flat filter'],
                'power_source' => 'electric', 'operator_type' => 'handheld',
                'power_watt' => 2400, 'voltage' => 220, 'tank' => 75, 'suction' => 254, 'airflow' => 5600,
                'weight' => 33, 'noise' => 73, 'price' => 265_000_000,
                'environments' => ['factory', 'warehouse', 'parking'],
                'specs' => [['تعداد موتور', 'Motor count', '۲ عدد', '2']],
                'faqs' => [],
            ],

            // -------------------------------------------------- Scrubbers ---
            [
                'slug' => 'arta-scrub-50b', 'category' => 'scrubber-dryer', 'brand' => 'artaclean',
                'model' => 'AS-50B', 'name_fa' => 'اسکرابر دستی باتری‌شارژی آرتا ۵۰', 'name_en' => 'Arta Scrub 50B',
                'short_fa' => 'اسکرابر دستی باتری‌شارژی با عرض شستشوی ۵۰ سانتی‌متر برای سالن‌های تا ۲۰۰۰ متر.',
                'short_en' => 'Battery walk-behind scrubber with a 500 mm path for floors up to 2,000 m².',
                'body_fa' => 'باتری ژل بدون نیاز به سرویس، ۲ ساعت کار مداوم می‌دهد. تیغه پارابولیک، کف را در یک پاس خشک تحویل می‌دهد.',
                'highlights_fa' => ['۲ ساعت کارکرد باتری', 'تیغه پارابولیک خشک‌کن', 'گردش در شعاع کم'],
                'highlights_en' => ['2 hour battery runtime', 'Parabolic drying squeegee', 'Tight turning radius'],
                'power_source' => 'battery', 'operator_type' => 'walk_behind',
                'power_watt' => 900, 'voltage' => 24, 'tank' => 50, 'recovery' => 55,
                'width' => 500, 'productivity' => 1800, 'weight' => 105, 'noise' => 62, 'runtime' => 120,
                'price' => 480_000_000, 'featured' => true, 'rentable' => true, 'warranty' => 24,
                'environments' => ['hospital', 'hotel', 'shopping-mall', 'factory'],
                'specs' => [
                    ['نوع باتری', 'Battery type', 'ژل ۲۴ ولت ۱۰۰ آمپر', '24 V 100 Ah gel'],
                    ['فشار براش', 'Brush pressure', '۲۵ کیلوگرم', '25 kg'],
                    ['نوع براش', 'Brush type', 'دیسکی تکی', 'Single disc'],
                ],
                'faqs' => [
                    ['برای سالن ۲۰۰۰ متری کافی است؟', 'بله. با راندمان واقعی حدود ۱۲۶۰ متر در ساعت، یک سالن ۲۰۰۰ متری در کمتر از دو ساعت شسته می‌شود.'],
                    ['باتری چقدر عمر می‌کند؟', 'باتری ژل در شارژ روزانه معمول، بین ۴۰۰ تا ۵۰۰ سیکل شارژ یعنی حدود دو سال کار می‌کند.'],
                    ['صدای دستگاه برای بیمارستان مناسب است؟', 'بله، سطح صدای ۶۲ دسی‌بل اجازه می‌دهد در ساعات فعال بیمارستان هم کار کند.'],
                ],
            ],
            [
                'slug' => 'arta-scrub-70', 'category' => 'scrubber-dryer', 'brand' => 'artaclean',
                'model' => 'ANA-70L', 'name_fa' => 'اسکرابر صنعتی آرتا ۷۰ لیتری', 'name_en' => 'Arta Scrub 70L',
                'short_fa' => 'اسکرابر دستی صنعتی با مخزن ۷۰ لیتری و عرض شستشوی ۶۵۰ میلی‌متر.',
                'short_en' => 'Industrial walk-behind scrubber, 70 litre tank and 650 mm cleaning path.',
                'body_fa' => 'دو براش دیسکی با فشار قابل تنظیم، لکه‌های چسبیده کف بتنی را بدون نیاز به پاس دوم برمی‌دارد.',
                'highlights_fa' => ['دو براش دیسکی', 'مخزن ۷۰ لیتر', 'راندمان ۲۶۰۰ متر در ساعت'],
                'highlights_en' => ['Twin disc brushes', '70 litre tank', '2,600 m²/h rated productivity'],
                'power_source' => 'battery', 'operator_type' => 'walk_behind',
                'power_watt' => 1500, 'voltage' => 24, 'tank' => 70, 'recovery' => 75,
                'width' => 650, 'productivity' => 2600, 'weight' => 148, 'noise' => 65, 'runtime' => 150,
                'price' => 720_000_000, 'featured' => true, 'rentable' => true, 'warranty' => 24,
                'environments' => ['factory', 'warehouse', 'shopping-mall', 'parking'],
                'specs' => [
                    ['تعداد براش', 'Brush count', '۲ عدد دیسکی', '2 disc'],
                    ['فشار براش', 'Brush pressure', '۴۰ کیلوگرم', '40 kg'],
                    ['شیب قابل عبور', 'Max gradient', '۲ درصد', '2%'],
                ],
                'faqs' => [
                    ['تفاوت این مدل با اسکرابر ۵۰ چیست؟', 'عرض شستشو ۱۵ سانتی‌متر بیشتر و مخزن ۲۰ لیتر بزرگ‌تر است؛ یعنی برای سالن‌های بالای ۲۰۰۰ متر توقف کمتری برای پر کردن مخزن دارید.'],
                    ['روی کف اپوکسی خط می‌اندازد؟', 'خیر، با پد سفید یا براش نرم روی اپوکسی و سنگ صیقلی بدون خط‌افتادگی کار می‌کند.'],
                ],
            ],
            [
                'slug' => 'karcher-bd-50-70', 'category' => 'scrubber-dryer', 'brand' => 'karcher',
                'model' => 'BD 50/70', 'name_fa' => 'اسکرابر کارچر BD 50/70', 'name_en' => 'Kärcher BD 50/70',
                'short_fa' => 'اسکرابر دستی کارچر با عرض ۵۱۰ میلی‌متر و مخزن ۷۰ لیتری.',
                'short_en' => 'Kärcher walk-behind scrubber, 510 mm path and 70 litre tank.',
                'body_fa' => 'سیستم KART امکان تعویض سریع براش بدون ابزار را می‌دهد.',
                'highlights_fa' => ['تعویض براش بدون ابزار', 'مخزن ۷۰ لیتر', 'باتری لیتیوم اختیاری'],
                'highlights_en' => ['Tool-free brush change', '70 litre tank', 'Optional lithium battery'],
                'power_source' => 'battery', 'operator_type' => 'walk_behind',
                'power_watt' => 1100, 'voltage' => 24, 'tank' => 70, 'recovery' => 70,
                'width' => 510, 'productivity' => 2000, 'weight' => 132, 'noise' => 63, 'runtime' => 135,
                'price' => 890_000_000, 'warranty' => 24,
                'environments' => ['shopping-mall', 'hospital', 'hotel', 'airport'],
                'specs' => [['تعویض براش', 'Brush change', 'بدون ابزار', 'Tool-free']],
                'faqs' => [],
            ],
            [
                'slug' => 'ipc-ct40', 'category' => 'scrubber-dryer', 'brand' => 'ipc',
                'model' => 'CT40', 'name_fa' => 'اسکرابر آی‌پی‌سی CT40', 'name_en' => 'IPC CT40',
                'short_fa' => 'اسکرابر دستی ایتالیایی با ساختار جمع‌وجور و عرض ۵۵۰ میلی‌متر.',
                'short_en' => 'Compact Italian walk-behind scrubber with a 550 mm path.',
                'body_fa' => 'ابعاد جمع‌وجور، این مدل را برای راهروهای باریک و فضاهای پر از مانع مناسب می‌کند.',
                'highlights_fa' => ['طراحی جمع‌وجور', 'مناسب راهروی باریک', 'تیغه استیل'],
                'highlights_en' => ['Compact footprint', 'Suits narrow aisles', 'Stainless squeegee'],
                'power_source' => 'battery', 'operator_type' => 'walk_behind',
                'power_watt' => 850, 'voltage' => 24, 'tank' => 40, 'recovery' => 45,
                'width' => 550, 'productivity' => 2100, 'weight' => 98, 'noise' => 60, 'runtime' => 110,
                'price' => 610_000_000, 'rentable' => true,
                'environments' => ['hotel', 'hospital', 'shopping-mall'],
                'specs' => [['شعاع گردش', 'Turning radius', '۸۰ سانتی‌متر', '80 cm']],
                'faqs' => [],
            ],
            [
                'slug' => 'arta-rider-100', 'category' => 'scrubber-dryer', 'brand' => 'artaclean',
                'model' => 'AR-100R', 'name_fa' => 'اسکرابر سرنشین‌دار آرتا ۱۰۰', 'name_en' => 'Arta Rider 100',
                'short_fa' => 'اسکرابر سرنشین‌دار با عرض ۱۰۰۰ میلی‌متر برای سالن‌های بالای ۵۰۰۰ متر.',
                'short_en' => 'Ride-on scrubber with a 1,000 mm path for floors above 5,000 m².',
                'body_fa' => 'راننده‌نشین با مخزن ۱۳۰ لیتری، یک شیفت کامل را بدون توقف طولانی پوشش می‌دهد.',
                'highlights_fa' => ['راندمان ۵۰۰۰ متر در ساعت', 'مخزن ۱۳۰ لیتری', 'باتری ۶ ساعته'],
                'highlights_en' => ['5,000 m²/h productivity', '130 litre tank', '6 hour battery'],
                'power_source' => 'battery', 'operator_type' => 'ride_on',
                'power_watt' => 3200, 'voltage' => 36, 'tank' => 130, 'recovery' => 140,
                'width' => 1000, 'productivity' => 5000, 'weight' => 420, 'noise' => 68, 'runtime' => 360,
                'price' => 2_400_000_000, 'featured' => true, 'rentable' => true, 'warranty' => 24,
                'environments' => ['warehouse', 'airport', 'parking', 'factory', 'shopping-mall'],
                'specs' => [
                    ['سرعت حرکت', 'Travel speed', '۶ کیلومتر بر ساعت', '6 km/h'],
                    ['شیب قابل عبور', 'Max gradient', '۱۰ درصد', '10%'],
                    ['نوع باتری', 'Battery type', 'ژل ۳۶ ولت ۲۴۰ آمپر', '36 V 240 Ah gel'],
                ],
                'faqs' => [
                    ['برای انبار ۲۰ هزار متری مناسب است؟', 'بله. با راندمان واقعی حدود ۴۰۰۰ متر در ساعت، ۲۰ هزار متر در یک شیفت هشت ساعته پوشش داده می‌شود.'],
                    ['آیا اپراتور به گواهینامه نیاز دارد؟', 'خیر، اما آموزش نیم‌روزه اپراتور همراه دستگاه ارائه می‌شود.'],
                ],
            ],

            // ------------------------------------------------- Polishers ----
            [
                'slug' => 'arta-polish-43', 'category' => 'floor-polisher', 'brand' => 'artaclean',
                'model' => 'AP-43', 'name_fa' => 'پولیشر صنعتی تک‌دیسک ۴۳ سانتی', 'name_en' => 'Arta Polisher 43',
                'short_fa' => 'پولیشر تک‌دیسک ۴۳ سانتی‌متری برای سایش، پولیش و شامپو موکت.',
                'short_en' => 'Single-disc 430 mm polisher for grinding, polishing and carpet shampooing.',
                'body_fa' => 'با تعویض پد، همان دستگاه هم سنگ را پولیش می‌کند و هم موکت را شامپو می‌زند.',
                'highlights_fa' => ['۱۷۵ دور بر دقیقه', 'دسته ارگونومیک قابل تنظیم', 'مخزن آب اختیاری'],
                'highlights_en' => ['175 rpm', 'Adjustable ergonomic handle', 'Optional water tank'],
                'power_source' => 'electric', 'operator_type' => 'walk_behind',
                'power_watt' => 1500, 'voltage' => 220, 'width' => 430, 'productivity' => 400,
                'weight' => 45, 'noise' => 60, 'price' => 96_000_000,
                'environments' => ['hotel', 'shopping-mall', 'hospital'],
                'specs' => [['دور موتور', 'Motor speed', '۱۷۵ دور بر دقیقه', '175 rpm']],
                'faqs' => [],
            ],

            // -------------------------------------------------- Sweepers ----
            [
                'slug' => 'arta-sweep-80', 'category' => 'industrial-sweeper', 'brand' => 'artaclean',
                'model' => 'ASW-80', 'name_fa' => 'سوییپر دستی آرتا ۸۰', 'name_en' => 'Arta Sweeper 80',
                'short_fa' => 'سوییپر دستی باتری‌شارژی با عرض جاروب ۸۰ سانتی‌متر.',
                'short_en' => 'Battery walk-behind sweeper with an 800 mm sweeping path.',
                'body_fa' => 'برس اصلی سیلندری به همراه یک برس جانبی، ضایعات را از کنار دیوار هم جمع می‌کند.',
                'highlights_fa' => ['برس جانبی برای کنار دیوار', 'مخزن ۵۰ لیتری', 'فیلتر پلی‌استر'],
                'highlights_en' => ['Side brush for edges', '50 litre hopper', 'Polyester filter'],
                'power_source' => 'battery', 'operator_type' => 'walk_behind',
                'power_watt' => 750, 'voltage' => 24, 'tank' => 50,
                'width' => 800, 'productivity' => 3600, 'weight' => 95, 'noise' => 66, 'runtime' => 180,
                'price' => 520_000_000, 'rentable' => true,
                'environments' => ['warehouse', 'factory', 'parking'],
                'specs' => [['تعداد برس جانبی', 'Side brushes', '۱ عدد', '1']],
                'faqs' => [],
            ],
            [
                'slug' => 'tennant-s20-rider', 'category' => 'industrial-sweeper', 'brand' => 'tennant',
                'model' => 'S20', 'name_fa' => 'سوییپر سرنشین‌دار تنانت S20', 'name_en' => 'Tennant S20 Rider',
                'short_fa' => 'سوییپر سرنشین‌دار دیزلی برای محوطه‌های باز و سایت‌های صنعتی.',
                'short_en' => 'Diesel ride-on sweeper for open yards and industrial sites.',
                'body_fa' => 'مخزن ۳۰۰ لیتری و تخلیه هیدرولیک از ارتفاع، توقف برای تخلیه را به حداقل می‌رساند.',
                'highlights_fa' => ['تخلیه هیدرولیک از ارتفاع', 'مخزن ۳۰۰ لیتری', 'مناسب محوطه باز'],
                'highlights_en' => ['High-dump hydraulic hopper', '300 litre hopper', 'Suited to open yards'],
                'power_source' => 'diesel', 'operator_type' => 'ride_on',
                'tank' => 300, 'width' => 1600, 'productivity' => 12000, 'weight' => 1450, 'noise' => 82,
                'price' => 6_800_000_000, 'stock' => 'on_order', 'warranty' => 24,
                'environments' => ['parking', 'factory', 'warehouse', 'airport'],
                'specs' => [
                    ['ارتفاع تخلیه', 'Dump height', '۱۵۰ سانتی‌متر', '150 cm'],
                    ['سوخت', 'Fuel', 'دیزل', 'Diesel'],
                ],
                'faqs' => [
                    ['برای فضای بسته هم قابل استفاده است؟', 'مدل دیزلی برای فضای باز طراحی شده است. برای سالن سرپوشیده نسخه باتری‌شارژی همین دستگاه پیشنهاد می‌شود.'],
                ],
            ],

            // --------------------------------------- Pressure & upholstery --
            [
                'slug' => 'arta-jet-200', 'category' => 'industrial-pressure-washer', 'brand' => 'artaclean',
                'model' => 'AJ-200', 'name_fa' => 'واترجت صنعتی آب سرد ۲۰۰ بار', 'name_en' => 'Arta Jet 200 Cold',
                'short_fa' => 'واترجت سه‌فاز آب سرد ۲۰۰ بار با پمپ برنجی سه‌پیستونه.',
                'short_en' => 'Three-phase 200 bar cold water pressure washer with a brass triplex pump.',
                'body_fa' => 'پمپ دور کند با روغن‌کاری اجباری، برای کار روزانه چند ساعته در کارواش طراحی شده است.',
                'highlights_fa' => ['پمپ برنجی سه‌پیستونه', 'دور کند ۱۴۰۰ rpm', 'شلنگ ۱۰ متری فولادی'],
                'highlights_en' => ['Brass triplex pump', 'Slow 1,400 rpm', '10 m steel-braided hose'],
                'power_source' => 'electric', 'operator_type' => 'stationary',
                'power_watt' => 5500, 'voltage' => 380, 'pressure' => 200, 'weight' => 92, 'noise' => 76,
                'price' => 310_000_000, 'featured' => true,
                'environments' => ['car-wash', 'factory', 'parking'],
                'specs' => [
                    ['دبی آب', 'Water flow', '۱۵ لیتر بر دقیقه', '15 L/min'],
                    ['دور پمپ', 'Pump speed', '۱۴۰۰ دور بر دقیقه', '1,400 rpm'],
                ],
                'faqs' => [
                    ['تفاوت پمپ دور کند و دور تند چیست؟', 'پمپ دور کند حرارت کمتری تولید می‌کند و عمر آب‌بندی‌ها چند برابر می‌شود؛ برای کارواشی که روزانه چند ساعت کار می‌کند انتخاب درست است.'],
                ],
            ],
            [
                'slug' => 'arta-steam-pro', 'category' => 'steam-cleaner', 'brand' => 'artaclean',
                'model' => 'AST-PRO', 'name_fa' => 'بخارشوی صنعتی آرتا پرو', 'name_en' => 'Arta Steam Pro',
                'short_fa' => 'بخارشوی صنعتی با بویلر استیل و بخار ۱۸۰ درجه برای ضدعفونی و چربی‌زدایی.',
                'short_en' => 'Industrial steam cleaner with a stainless boiler and 180 °C dry steam.',
                'body_fa' => 'بخار خشک اشباع، بدون مواد شیمیایی چربی و آلودگی میکروبی را از بین می‌برد.',
                'highlights_fa' => ['بخار ۱۸۰ درجه', 'بویلر استیل', 'مکش همزمان'],
                'highlights_en' => ['180 °C steam', 'Stainless boiler', 'Simultaneous extraction'],
                'power_source' => 'electric', 'operator_type' => 'handheld',
                'power_watt' => 3400, 'voltage' => 220, 'tank' => 10, 'steam' => 180,
                'weight' => 38, 'noise' => 65, 'price' => 240_000_000,
                'environments' => ['hospital', 'hotel', 'car-wash', 'factory'],
                'specs' => [
                    ['فشار بخار', 'Steam pressure', '۱۰ بار', '10 bar'],
                    ['زمان گرم شدن', 'Heat-up time', '۸ دقیقه', '8 min'],
                ],
                'faqs' => [
                    ['آیا برای ضدعفونی اتاق بیمار مناسب است؟', 'بله، بخار خشک ۱۸۰ درجه بدون استفاده از مواد شیمیایی، بار میکروبی سطوح را کاهش می‌دهد.'],
                ],
            ],
            [
                'slug' => 'arta-extract-40', 'category' => 'upholstery-cleaner', 'brand' => 'artaclean',
                'model' => 'AE-40', 'name_fa' => 'مبل شوی صنعتی آرتا ۴۰', 'name_en' => 'Arta Extractor 40',
                'short_fa' => 'مبل شوی تزریق و مکش ۴۰ لیتری برای مبلمان، موکت و صندلی خودرو.',
                'short_en' => '40 litre injection-extraction machine for upholstery, carpet and car seats.',
                'body_fa' => 'پمپ ۵ بار و مکش دو موتوره، رطوبت باقیمانده را به حداقل می‌رساند تا موکت زودتر خشک شود.',
                'highlights_fa' => ['پمپ ۵ بار', 'مکش دو موتوره', 'گرمکن آب اختیاری'],
                'highlights_en' => ['5 bar pump', 'Twin-motor extraction', 'Optional water heater'],
                'power_source' => 'electric', 'operator_type' => 'handheld',
                'power_watt' => 1800, 'voltage' => 220, 'tank' => 40, 'recovery' => 38,
                'suction' => 230, 'weight' => 32, 'noise' => 70, 'price' => 148_000_000, 'rentable' => true,
                'environments' => ['hotel', 'car-wash', 'shopping-mall'],
                'specs' => [['فشار پمپ', 'Pump pressure', '۵ بار', '5 bar']],
                'faqs' => [],
            ],

            // ------------------------------------ Spare parts & chemicals ---
            [
                'slug' => 'squeegee-blade-set-70', 'category' => 'spare-parts', 'brand' => 'artaclean',
                'model' => 'SB-70', 'name_fa' => 'ست تیغه لاستیکی اسکرابر ۷۰', 'name_en' => 'Squeegee Blade Set 70',
                'short_fa' => 'ست تیغه جلو و عقب پلی‌اورتان مناسب اسکرابر آرتا ۷۰.',
                'short_en' => 'Front and rear polyurethane squeegee blades for the Arta Scrub 70.',
                'body_fa' => 'پلی‌اورتان در برابر روغن و مواد شیمیایی مقاوم‌تر از لاستیک طبیعی است.',
                'highlights_fa' => ['جنس پلی‌اورتان', 'مقاوم در برابر روغن'],
                'highlights_en' => ['Polyurethane', 'Oil resistant'],
                'power_source' => 'manual', 'weight' => 2, 'price' => 8_500_000, 'warranty' => 3,
                'environments' => ['factory', 'warehouse'],
                'specs' => [['تعداد در بسته', 'Pieces per set', '۲ عدد', '2']],
                'faqs' => [],
            ],
            [
                'slug' => 'arta-degreaser-20l', 'category' => 'cleaning-chemicals', 'brand' => 'artaclean',
                'model' => 'ACD-20', 'name_fa' => 'چربی‌زدای صنعتی آرتا ۲۰ لیتری', 'name_en' => 'Arta Degreaser 20L',
                'short_fa' => 'چربی‌زدای قلیایی کم‌کف مخصوص اسکرابر، مناسب کف بتنی و اپوکسی.',
                'short_en' => 'Low-foam alkaline degreaser formulated for scrubber dryers.',
                'body_fa' => 'فرمول کم‌کف مانع پر شدن زودهنگام مخزن بازیافت و آسیب به موتور مکش می‌شود.',
                'highlights_fa' => ['کم‌کف مخصوص اسکرابر', 'رقت ۱ به ۵۰', 'بدون آسیب به اپوکسی'],
                'highlights_en' => ['Low foam for scrubbers', '1:50 dilution', 'Epoxy safe'],
                'power_source' => 'manual', 'tank' => 20, 'weight' => 21, 'price' => 12_000_000, 'warranty' => 0,
                'environments' => ['factory', 'warehouse', 'parking'],
                'specs' => [
                    ['pH', 'pH', '۱۱', '11'],
                    ['نسبت رقت', 'Dilution', '۱ به ۵۰', '1:50'],
                ],
                'faqs' => [
                    ['چرا نباید از شوینده معمولی در اسکرابر استفاده کرد؟', 'شوینده پرکف، مخزن بازیافت را زود پر می‌کند و کف وارد موتور مکش می‌شود که به مرور موتور را از کار می‌اندازد.'],
                ],
            ],
        ];
    }
}
