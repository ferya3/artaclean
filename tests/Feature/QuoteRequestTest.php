<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Jobs\NotifySalesOfNewLead;
use App\Livewire\QuoteRequestForm;
use App\Models\Category;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class QuoteRequestTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $category = Category::create(['slug' => 'scrubber-dryer', 'name' => ['fa' => 'اسکرابر']]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'slug' => 'arta-scrub-70',
            'name' => ['fa' => 'اسکرابر آرتا ۷۰'],
            'power_source' => 'battery',
            'price' => 720_000_000,
            'is_active' => true,
            'is_rentable' => true,
        ]);
    }

    public function test_it_captures_a_lead_and_a_quote(): void
    {
        Livewire::test(QuoteRequestForm::class, ['productId' => $this->product->id])
            ->set('name', 'محمد رضایی')
            ->set('phone', '09121234567')
            ->set('city', 'تهران')
            ->set('company', 'صنایع پارس')
            ->set('businessType', 'factory')
            ->set('quantity', 2)
            ->set('note', 'سالن ۵۰۰۰ متری')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $lead = Lead::sole();

        $this->assertSame('محمد رضایی', $lead->name);
        $this->assertSame(LeadSource::Quote, $lead->source);
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertSame($this->product->id, $lead->product_id);

        $quote = Quote::sole();

        $this->assertSame($lead->id, $quote->lead_id);
        $this->assertSame(2, $quote->quantity);
        $this->assertSame('purchase', $quote->type);
        $this->assertNotNull($quote->valid_until);

        $this->assertSame(1, $this->product->fresh()->quotes_count);

        Queue::assertPushed(NotifySalesOfNewLead::class);
    }

    public function test_it_scores_a_factory_buyer_above_an_anonymous_enquiry(): void
    {
        Livewire::test(QuoteRequestForm::class, ['productId' => $this->product->id])
            ->set('name', 'مدیر تدارکات')
            ->set('phone', '09121112233')
            ->set('company', 'فولاد مبارکه')
            ->set('email', 'buyer@example.com')
            ->set('businessType', 'factory')
            ->call('submit')
            ->assertHasNoErrors();

        // quote(40) + factory(25) + company(10) + email(5) + product(10)
        // + high-ticket machine(0, price is under 1B) = 90
        $this->assertSame(90, Lead::sole()->score);
    }

    public function test_a_rental_request_records_the_period(): void
    {
        Livewire::test(QuoteRequestForm::class, ['productId' => $this->product->id, 'mode' => 'rental'])
            ->set('name', 'نگین موسوی')
            ->set('phone', '09354445566')
            ->set('rentalPeriod', 'monthly')
            ->set('rentalDuration', 3)
            ->call('submit')
            ->assertHasNoErrors();

        $quote = Quote::sole();

        $this->assertSame('rental', $quote->type);
        $this->assertSame('monthly', $quote->rental_period->value);
        $this->assertSame(3, $quote->rental_duration);
        $this->assertSame(LeadSource::Rental, Lead::sole()->source);
    }

    public function test_a_second_enquiry_from_the_same_number_reuses_the_open_lead(): void
    {
        $submit = fn () => Livewire::test(QuoteRequestForm::class, ['productId' => $this->product->id])
            ->set('name', 'علی کریمی')
            ->set('phone', '09131112233')
            ->call('submit')
            ->assertHasNoErrors();

        $submit();
        $submit();

        // One buyer, one pipeline entry — but both quotes are recorded.
        $this->assertSame(1, Lead::count());
        $this->assertSame(2, Quote::count());
    }

    public function test_it_rejects_an_invalid_phone_number(): void
    {
        Livewire::test(QuoteRequestForm::class, ['productId' => $this->product->id])
            ->set('name', 'تست کاربر')
            ->set('phone', 'not-a-number')
            ->call('submit')
            ->assertHasErrors('phone');

        $this->assertSame(0, Lead::count());
    }
}
