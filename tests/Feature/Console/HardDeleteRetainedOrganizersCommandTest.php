<?php

namespace Tests\Feature\Console;

use App\Domain\Constants\AdminPanelConstants;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class HardDeleteRetainedOrganizersCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('GIVEN an organizer soft-deleted 31 days ago WHEN the purge command runs THEN personal fields are scrubbed')]
    public function it_scrubs_personal_fields_past_the_retention_window(): void
    {
        $organizer = Organizer::factory()->approved()->create([
            'org_name' => 'Acme Events',
            'email' => 'acme@example.com',
        ]);
        $organizer->delete();
        $organizer->forceFill(['deleted_at' => now()->subDays(31)])->save();

        $this->artisan('organizers:purge-retained-data')->assertSuccessful();

        $organizer->refresh();
        $this->assertSame(AdminPanelConstants::PURGED_PERSONAL_DATA_PLACEHOLDER, $organizer->org_name);
        $this->assertNotSame('acme@example.com', $organizer->email);
        $this->assertNotNull($organizer->personal_data_purged_at);
    }

    #[Test]
    #[TestDox('GIVEN an organizer soft-deleted 10 days ago WHEN the purge command runs THEN personal fields are left untouched')]
    public function it_leaves_recently_deleted_organizers_untouched(): void
    {
        $organizer = Organizer::factory()->approved()->create(['org_name' => 'Acme Events']);
        $organizer->delete();
        $organizer->forceFill(['deleted_at' => now()->subDays(10)])->save();

        $this->artisan('organizers:purge-retained-data')->assertSuccessful();

        $organizer->refresh();
        $this->assertSame('Acme Events', $organizer->org_name);
        $this->assertNull($organizer->personal_data_purged_at);
    }

    #[Test]
    #[TestDox('GIVEN an organizer already purged WHEN the purge command runs again THEN it is skipped')]
    public function it_is_idempotent_for_already_purged_organizers(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $organizer->delete();
        $organizer->forceFill([
            'deleted_at' => now()->subDays(31),
            'personal_data_purged_at' => now()->subDays(1),
            'org_name' => AdminPanelConstants::PURGED_PERSONAL_DATA_PLACEHOLDER,
        ])->save();
        $purgedAt = $organizer->personal_data_purged_at;

        $this->artisan('organizers:purge-retained-data')->assertSuccessful();

        $organizer->refresh();
        $this->assertTrue($purgedAt->equalTo($organizer->personal_data_purged_at));
    }
}
