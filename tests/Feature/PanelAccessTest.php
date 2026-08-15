<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Dealer;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function user(string $role, ?Dealer $dealer = null): User
    {
        $user = User::create([
            'name' => 'Test '.$role,
            'email' => $role.'@example.com',
            'password' => 'password',
            'is_active' => true,
            'dealer_id' => $dealer?->id,
        ]);

        $user->roles()->attach(Role::where('slug', $role)->value('id'));

        return $user->fresh();
    }

    public function test_the_login_screens_are_reachable(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/dealer/login')->assertOk();
    }

    /*
     * One signed-in user per test: Filament's AuthenticateSession middleware
     * invalidates the session when the authenticated user changes mid-test,
     * which would show up as a spurious redirect.
     */

    public function test_an_administrator_reaches_the_admin_panel(): void
    {
        $this->actingAs($this->user(Role::ADMIN))->get('/admin')->assertSuccessful();
    }

    public function test_a_sales_user_reaches_the_admin_panel(): void
    {
        $this->actingAs($this->user(Role::SALES))->get('/admin')->assertSuccessful();
    }

    public function test_a_customer_cannot_reach_the_admin_panel(): void
    {
        $this->actingAs($this->user(Role::CUSTOMER))->get('/admin')->assertForbidden();
    }

    public function test_a_deactivated_account_is_locked_out(): void
    {
        $user = $this->user(Role::ADMIN);
        $user->update(['is_active' => false]);

        $this->actingAs($user->fresh())->get('/admin')->assertForbidden();
    }

    public function test_a_dealer_without_a_dealership_cannot_reach_the_dealer_panel(): void
    {
        $this->actingAs($this->user(Role::DEALER))->get('/dealer')->assertForbidden();
    }

    public function test_a_dealer_reaches_its_own_panel(): void
    {
        $dealer = Dealer::create(['slug' => 'tehran', 'name' => 'نمایندگی تهران', 'is_active' => true]);

        $this->actingAs($this->user(Role::DEALER, $dealer))->get('/dealer')->assertSuccessful();
    }

    /**
     * The dealer panel applies a global scope in middleware, so a dealer must
     * never be able to enumerate another dealer's pipeline.
     */
    public function test_the_dealer_panel_scopes_leads_to_the_signed_in_dealer(): void
    {
        $mine = Dealer::create(['slug' => 'mine', 'name' => 'نمایندگی من', 'is_active' => true]);
        $theirs = Dealer::create(['slug' => 'theirs', 'name' => 'نمایندگی دیگر', 'is_active' => true]);

        Lead::create(['name' => 'سرنخ من', 'phone' => '09120000001', 'dealer_id' => $mine->id]);
        Lead::create(['name' => 'سرنخ دیگری', 'phone' => '09120000002', 'dealer_id' => $theirs->id]);

        $this->actingAs($this->user(Role::DEALER, $mine))
            ->get('/dealer/leads')
            ->assertSuccessful()
            ->assertSee('سرنخ من')
            ->assertDontSee('سرنخ دیگری');
    }
}
