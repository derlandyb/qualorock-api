<?php

namespace Tests\Feature\SuperAdmin;

use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\PlanPrice;
use App\Infrastructure\Persistence\Eloquent\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class PlanPricingControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('GIVEN a plan price history WHEN a super admin lists it THEN the current and historical rows are returned')]
    public function it_lists_current_and_historical_plan_prices(): void
    {
        $superAdmin = SuperAdmin::factory()->create();
        $previous = PlanPrice::factory()->create([
            'amount' => 1990,
            'effective_from' => now()->subMonths(2),
            'effective_to' => now()->subMonth(),
        ]);
        $current = PlanPrice::factory()->create([
            'amount' => 2990,
            'effective_from' => now()->subMonth(),
            'effective_to' => null,
        ]);

        $response = $this->actingAs($superAdmin, 'super_admin')->getJson('/api/admin/v1/super-admin/plan-prices');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $current->id, 'amount' => 2990, 'effectiveTo' => null]);
        $response->assertJsonFragment(['id' => $previous->id, 'amount' => 1990]);
        $ids = array_column($response->json('data'), 'id');
        $this->assertSame([$current->id, $previous->id], $ids);
    }

    #[Test]
    #[TestDox('GIVEN a super admin sets a new Plus price WHEN reading history THEN the new row is current and the previous row is closed')]
    public function it_sets_a_new_plus_price_and_closes_the_previous_one(): void
    {
        $superAdmin = SuperAdmin::factory()->create();
        $previous = PlanPrice::factory()->create(['amount' => 1990, 'effective_to' => null]);

        $response = $this->actingAs($superAdmin, 'super_admin')
            ->postJson('/api/admin/v1/super-admin/plan-prices', ['amount' => 2990]);

        $response->assertCreated();
        $response->assertJsonFragment(['amount' => 2990, 'effectiveTo' => null]);
        $this->assertNotNull($previous->fresh()->effective_to);
    }

    #[Test]
    #[TestDox('GIVEN an already-closed historical row WHEN a super admin sets a new price THEN that older row is left untouched')]
    public function it_leaves_already_closed_historical_rows_untouched_when_setting_a_new_price(): void
    {
        $superAdmin = SuperAdmin::factory()->create();
        $closedAt = now()->subMonths(2)->startOfSecond();
        $olderClosed = PlanPrice::factory()->create([
            'amount' => 990,
            'effective_from' => now()->subMonths(3),
            'effective_to' => $closedAt,
        ]);
        $current = PlanPrice::factory()->create([
            'amount' => 1990,
            'effective_from' => $closedAt,
            'effective_to' => null,
        ]);

        $this->actingAs($superAdmin, 'super_admin')
            ->postJson('/api/admin/v1/super-admin/plan-prices', ['amount' => 2990])
            ->assertCreated();

        $this->assertTrue($olderClosed->fresh()->effective_to->equalTo($closedAt));
        $this->assertNotNull($current->fresh()->effective_to);
    }

    #[Test]
    #[TestDox('GIVEN a non-numeric or negative amount WHEN a super admin submits it THEN the response is 422')]
    public function it_rejects_a_non_numeric_or_negative_amount(): void
    {
        $superAdmin = SuperAdmin::factory()->create();

        foreach (['abc', -5, 0] as $invalidAmount) {
            $response = $this->actingAs($superAdmin, 'super_admin')
                ->postJson('/api/admin/v1/super-admin/plan-prices', ['amount' => $invalidAmount]);

            $response->assertStatus(422);
        }
    }

    #[Test]
    #[TestDox('GIVEN an organizer, not a super admin, WHEN calling either plan-pricing endpoint THEN the response is 403')]
    public function it_denies_a_non_super_admin(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        PlanPrice::factory()->create();

        $response = $this->actingAs($organizer, 'organizer')->getJson('/api/admin/v1/super-admin/plan-prices');
        $response->assertForbidden();

        $response = $this->actingAs($organizer, 'organizer')
            ->postJson('/api/admin/v1/super-admin/plan-prices', ['amount' => 2990]);
        $response->assertForbidden();
    }
}
