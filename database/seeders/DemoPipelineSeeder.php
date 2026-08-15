<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\QuoteStatus;
use App\Models\Dealer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use App\Services\LeadScoringService;
use Illuminate\Database\Seeder;

/**
 * A handful of leads and quotes so the dashboard, the pipeline filters and the
 * dealer panel have something to show on a fresh install.
 */
class DemoPipelineSeeder extends Seeder
{
    public function run(LeadScoringService $scoring): void
    {
        if (Lead::query()->exists()) {
            return;
        }

        $products = Product::query()->where('is_featured', true)->get();
        $sales = User::query()->whereHas('roles', fn ($q) => $q->where('slug', 'sales'))->first();
        $dealers = Dealer::query()->pluck('id');

        $samples = [
            ['محمد رضایی', '09121234567', 'تهران', 'صنایع غذایی پارس', 'factory', LeadSource::Quote, LeadStatus::New, 5200],
            ['سارا احمدی', '09129876543', 'اصفهان', 'بیمارستان مهر', 'hospital', LeadSource::Calculator, LeadStatus::Contacted, 1800],
            ['علی کریمی', '09131112233', 'مشهد', 'مجتمع تجاری آلماس', 'shopping-mall', LeadSource::Quote, LeadStatus::Qualified, 24000],
            ['نگین موسوی', '09354445566', 'کرج', 'انبار مرکزی دیجی', 'warehouse', LeadSource::Rental, LeadStatus::Proposal, 32000],
            ['حسین نوری', '09197778899', 'تبریز', 'کارواش نور', 'car-wash', LeadSource::Whatsapp, LeadStatus::Won, 600],
            ['مریم قاسمی', '09362223344', 'شیراز', 'هتل پارسیان', 'hotel', LeadSource::Catalog, LeadStatus::Lost, 4500],
        ];

        foreach ($samples as $index => [$name, $phone, $city, $company, $business, $source, $status, $area]) {
            $product = $products->get($index % max($products->count(), 1));

            $lead = Lead::create([
                'name' => $name,
                'phone' => $phone,
                'email' => 'lead'.$index.'@example.com',
                'city' => $city,
                'company' => $company,
                'business_type' => $business,
                'product_id' => $product?->id,
                'dealer_id' => $index % 2 === 0 ? $dealers->get($index % max($dealers->count(), 1)) : null,
                'assigned_to' => $status === LeadStatus::New ? null : $sales?->id,
                'source' => $source,
                'status' => $status,
                'message' => "متراژ سالن حدود {$area} متر مربع است.",
                'meta' => ['area' => $area],
                'created_at' => now()->subDays(random_int(0, 20)),
                'last_contacted_at' => $status === LeadStatus::New ? null : now()->subDays(random_int(0, 5)),
                'closed_at' => $status->isClosed() ? now()->subDays(random_int(0, 3)) : null,
            ]);

            $lead->update([
                'score' => $scoring->score($lead),
                'estimated_value' => $scoring->estimateValue($lead),
            ]);

            if (in_array($source, [LeadSource::Quote, LeadSource::Rental], true)) {
                Quote::create([
                    'lead_id' => $lead->id,
                    'product_id' => $product?->id,
                    'dealer_id' => $lead->dealer_id,
                    'type' => $source === LeadSource::Rental ? 'rental' : 'purchase',
                    'quantity' => 1,
                    'rental_period' => $source === LeadSource::Rental ? 'monthly' : null,
                    'rental_duration' => $source === LeadSource::Rental ? 3 : null,
                    'status' => $status === LeadStatus::New ? QuoteStatus::Pending : QuoteStatus::Sent,
                    'quoted_price' => $status === LeadStatus::New ? null : $product?->price,
                    'valid_until' => now()->addDays(14),
                    'customer_note' => $lead->message,
                ]);
            }
        }
    }
}
