<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Environment;
use Illuminate\Database\Seeder;

/**
 * Brands, categories, environments and the filterable attribute set.
 * Category slugs are the English SEO landing URLs that sit at the domain root.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->brands();
        $this->categories();
        $this->environments();
        $this->attributes();
    }

    private function brands(): void
    {
        $brands = [
            ['karcher', 'کارچر', 'Kärcher', 'آلمان', 'Germany'],
            ['nilfisk', 'نیلفیسک', 'Nilfisk', 'دانمارک', 'Denmark'],
            ['tennant', 'تنانت', 'Tennant', 'آمریکا', 'United States'],
            ['ipc', 'آی‌پی‌سی', 'IPC', 'ایتالیا', 'Italy'],
            ['comac', 'کوماک', 'Comac', 'ایتالیا', 'Italy'],
            ['fimap', 'فیمپ', 'Fimap', 'ایتالیا', 'Italy'],
            ['artaclean', 'آرتاکلین', 'ArtaClean', 'ایران', 'Iran'],
        ];

        foreach ($brands as $index => [$slug, $nameFa, $nameEn, $countryFa, $countryEn]) {
            Brand::updateOrCreate(['slug' => $slug], [
                'name' => ['fa' => $nameFa, 'en' => $nameEn],
                'country' => app()->getLocale() === 'en' ? $countryEn : $countryFa,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }

    private function categories(): void
    {
        $categories = [
            [
                'industrial-vacuum-cleaner', 'جاروبرقی صنعتی', 'Industrial Vacuum Cleaner',
                'مکش قدرتمند برای خط تولید، انبار و کارگاه — از مدل‌های تک‌فاز تا سه‌فاز صنعتی.',
                'Heavy-duty suction for production lines, warehouses and workshops.',
            ],
            [
                'scrubber-dryer', 'اسکرابر صنعتی', 'Scrubber Dryer',
                'شستشو، سایش و خشک‌کن در یک پاس؛ از اسکرابر دستی تا سرنشین‌دار.',
                'Scrub, agitate and dry in a single pass — walk-behind to ride-on.',
            ],
            [
                'floor-polisher', 'پولیشر صنعتی', 'Floor Polisher',
                'پولیش، براق‌سازی و احیای کف سنگ و اپوکسی.',
                'Polishing, burnishing and restoration for stone and epoxy floors.',
            ],
            [
                'industrial-sweeper', 'سوییپر صنعتی', 'Industrial Sweeper',
                'جمع‌آوری ضایعات خشک در محوطه‌های بزرگ و فضای باز.',
                'Dry debris collection across large indoor and outdoor areas.',
            ],
            [
                'upholstery-cleaner', 'مبل شوی صنعتی', 'Upholstery Cleaner',
                'شستشوی مبلمان، موکت و صندلی با تزریق و مکش.',
                'Injection-extraction cleaning for upholstery, carpet and seating.',
            ],
            [
                'industrial-pressure-washer', 'کارواش صنعتی', 'Industrial Pressure Washer',
                'واترجت صنعتی آب سرد و گرم برای کارواش و صنایع.',
                'Cold and hot water pressure washers for car washes and industry.',
            ],
            [
                'steam-cleaner', 'بخارشوی صنعتی', 'Steam Cleaner',
                'ضدعفونی و چربی‌زدایی با بخار خشک اشباع.',
                'Sanitising and degreasing with saturated dry steam.',
            ],
            [
                'spare-parts', 'قطعات یدکی', 'Spare Parts',
                'تیغه، برس، فیلتر، باتری و قطعات مصرفی تمام برندها.',
                'Squeegees, brushes, filters, batteries and consumables for every brand.',
            ],
            [
                'cleaning-chemicals', 'مواد شوینده صنعتی', 'Cleaning Chemicals',
                'شوینده، چربی‌زدا و ضدکف مناسب دستگاه‌های صنعتی.',
                'Detergents, degreasers and defoamers formulated for machines.',
            ],
        ];

        foreach ($categories as $index => [$slug, $nameFa, $nameEn, $descFa, $descEn]) {
            Category::updateOrCreate(['slug' => $slug], [
                'name' => ['fa' => $nameFa, 'en' => $nameEn],
                'short_description' => ['fa' => $descFa, 'en' => $descEn],
                'seo_title' => [
                    'fa' => "خرید {$nameFa} | قیمت روز و مشخصات فنی",
                    'en' => "{$nameEn} — specifications and pricing",
                ],
                'seo_description' => ['fa' => $descFa, 'en' => $descEn],
                'sort_order' => $index,
                'is_active' => true,
                'show_in_menu' => true,
            ]);
        }

        // A dedicated wet & dry landing page under the vacuum category, because
        // "wet dry vacuum" is its own search intent.
        $parent = Category::where('slug', 'industrial-vacuum-cleaner')->first();

        Category::updateOrCreate(['slug' => 'wet-dry-vacuum'], [
            'parent_id' => $parent?->id,
            'name' => ['fa' => 'جاروبرقی آب و خاک', 'en' => 'Wet & Dry Vacuum'],
            'short_description' => [
                'fa' => 'مکش همزمان مایعات و ضایعات خشک؛ مناسب کارگاه و کارواش.',
                'en' => 'Simultaneous liquid and dry debris pickup for workshops and car washes.',
            ],
            'sort_order' => 1,
            'is_active' => true,
            'show_in_menu' => false,
        ]);
    }

    private function environments(): void
    {
        $environments = [
            [
                'factory', 'کارخانه', 'Factory', 2000, 40000,
                'کف بتنی و اپوکسی، روغن و براده، تردد لیفتراک.',
                'Concrete and epoxy floors, oil and swarf, forklift traffic.',
                ['روغن و گریس روی کف', 'براده فلزی و ضایعات', 'کار در سه شیفت', 'ایمنی در مسیر لیفتراک'],
                ['Oil and grease on the floor', 'Metal swarf and debris', 'Three-shift operation', 'Safety around forklift routes'],
            ],
            [
                'warehouse', 'انبار و مرکز لجستیک', 'Warehouse', 5000, 60000,
                'راهروهای طولانی و باز، گرد و غبار، سطح وسیع.',
                'Long open aisles, dust, very large areas.',
                ['متراژ بسیار زیاد', 'گرد و غبار پالت', 'راهروهای باریک بین قفسه', 'زمان محدود بین شیفت‌ها'],
                ['Very large floor area', 'Pallet dust', 'Narrow racking aisles', 'Short windows between shifts'],
            ],
            [
                'hospital', 'بیمارستان', 'Hospital', 500, 20000,
                'الزام بهداشتی، صدای کم، کار در ساعات فعال.',
                'Hygiene requirements, low noise, cleaning during active hours.',
                ['ضدعفونی و کنترل عفونت', 'حداکثر صدای مجاز پایین', 'راهروهای شلوغ', 'کف وینیل حساس'],
                ['Disinfection and infection control', 'Strict noise limits', 'Busy corridors', 'Sensitive vinyl flooring'],
            ],
            [
                'shopping-mall', 'مرکز خرید', 'Shopping Mall', 3000, 50000,
                'کف سنگ براق، تردد بالا، نظافت در حضور مشتری.',
                'Polished stone, heavy footfall, cleaning in front of customers.',
                ['حفظ براقیت سنگ', 'تردد بالا و لکه‌های مکرر', 'نظافت بدون مزاحمت', 'موانع و ویترین‌ها'],
                ['Keeping stone glossy', 'Heavy footfall and frequent spills', 'Cleaning without disruption', 'Obstacles and displays'],
            ],
            [
                'hotel', 'هتل', 'Hotel', 300, 15000,
                'موکت، لابی سنگی و مبلمان؛ صدای کم اهمیت حیاتی دارد.',
                'Carpet, stone lobbies and upholstery; low noise is critical.',
                ['موکت و مبلمان', 'کار در ساعات آرام', 'صدای بسیار کم', 'فضاهای متنوع'],
                ['Carpet and upholstery', 'Working in quiet hours', 'Very low noise', 'Mixed surfaces'],
            ],
            [
                'parking', 'پارکینگ', 'Car Park', 2000, 80000,
                'کف بتنی خشن، لاستیک‌سوختگی و روغن، تهویه محدود.',
                'Rough concrete, tyre marks and oil, limited ventilation.',
                ['لکه روغن و لاستیک', 'کف بتنی ساینده', 'شیب رمپ‌ها', 'تهویه محدود برای موتور احتراقی'],
                ['Oil and tyre marks', 'Abrasive concrete', 'Ramp gradients', 'Limited ventilation for combustion engines'],
            ],
            [
                'airport', 'فرودگاه و ترمینال', 'Airport', 10000, 150000,
                'متراژ بسیار بزرگ، عملیات ۲۴ ساعته، سطح براق.',
                'Very large areas, 24-hour operation, polished surfaces.',
                ['عملیات شبانه‌روزی', 'متراژ ده‌ها هزار متری', 'حضور دائم مسافر', 'الزام ظاهری بالا'],
                ['Round-the-clock operation', 'Tens of thousands of square metres', 'Constant passenger presence', 'High appearance standard'],
            ],
            [
                'car-wash', 'کارواش', 'Car Wash', 100, 3000,
                'آب فراوان، کف لغزنده، چربی و شامپو.',
                'Constant water, slippery floors, grease and shampoo.',
                ['حجم بالای آب روی کف', 'چربی و شامپو', 'خوردگی تجهیزات', 'نیاز به فشار آب بالا'],
                ['Standing water', 'Grease and shampoo', 'Equipment corrosion', 'High water pressure demand'],
            ],
        ];

        foreach ($environments as $index => $data) {
            [$slug, $nameFa, $nameEn, $areaMin, $areaMax, $descFa, $descEn, $challengesFa, $challengesEn] = $data;

            Environment::updateOrCreate(['slug' => $slug], [
                'name' => ['fa' => $nameFa, 'en' => $nameEn],
                'short_description' => ['fa' => $descFa, 'en' => $descEn],
                'challenges' => ['fa' => $challengesFa, 'en' => $challengesEn],
                'typical_area_min' => $areaMin,
                'typical_area_max' => $areaMax,
                'seo_title' => [
                    'fa' => "دستگاه نظافت صنعتی مناسب {$nameFa}",
                    'en' => "Industrial cleaning machines for {$nameEn}",
                ],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }

    private function attributes(): void
    {
        $attributes = [
            [
                'tank-material', ['fa' => 'جنس مخزن', 'en' => 'Tank material'], 'select',
                [
                    ['polyethylene', ['fa' => 'پلی‌اتیلن', 'en' => 'Polyethylene']],
                    ['stainless-steel', ['fa' => 'استیل ضدزنگ', 'en' => 'Stainless steel']],
                ],
                ['industrial-vacuum-cleaner', 'scrubber-dryer', 'wet-dry-vacuum'],
            ],
            [
                'filtration', ['fa' => 'نوع فیلتر', 'en' => 'Filtration'], 'select',
                [
                    ['standard', ['fa' => 'استاندارد', 'en' => 'Standard']],
                    ['hepa-h13', ['fa' => 'هپا H13', 'en' => 'HEPA H13']],
                    ['hepa-h14', ['fa' => 'هپا H14', 'en' => 'HEPA H14']],
                ],
                ['industrial-vacuum-cleaner', 'wet-dry-vacuum'],
            ],
            [
                'brush-type', ['fa' => 'نوع براش', 'en' => 'Brush type'], 'select',
                [
                    ['disc', ['fa' => 'دیسکی', 'en' => 'Disc']],
                    ['cylindrical', ['fa' => 'سیلندری', 'en' => 'Cylindrical']],
                ],
                ['scrubber-dryer', 'floor-polisher'],
            ],
            [
                'water-temperature', ['fa' => 'دمای آب', 'en' => 'Water temperature'], 'select',
                [
                    ['cold', ['fa' => 'آب سرد', 'en' => 'Cold water']],
                    ['hot', ['fa' => 'آب گرم', 'en' => 'Hot water']],
                ],
                ['industrial-pressure-washer'],
            ],
        ];

        foreach ($attributes as $index => [$slug, $name, $type, $values, $categorySlugs]) {
            $attribute = Attribute::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'type' => $type,
                'is_filterable' => true,
                'is_comparable' => true,
                'sort_order' => $index,
            ]);

            foreach ($values as $order => [$valueSlug, $value]) {
                AttributeValue::updateOrCreate(
                    ['attribute_id' => $attribute->id, 'slug' => $valueSlug],
                    ['value' => $value, 'sort_order' => $order],
                );
            }

            $attribute->categories()->sync(
                Category::whereIn('slug', $categorySlugs)->pluck('id')
            );
        }
    }
}
