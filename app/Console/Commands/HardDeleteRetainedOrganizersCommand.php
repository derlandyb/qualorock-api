<?php

namespace App\Console\Commands;

use App\Application\UseCases\OrganizerData\PurgeRetainedOrganizerPersonalData;
use Illuminate\Console\Command;

class HardDeleteRetainedOrganizersCommand extends Command
{
    protected $signature = 'organizers:purge-retained-data';

    protected $description = 'Hard-delete personal fields of organizers whose deletion retention window has elapsed.';

    public function handle(PurgeRetainedOrganizerPersonalData $purgeRetainedOrganizerPersonalData): int
    {
        $purgedCount = $purgeRetainedOrganizerPersonalData->handle();

        $this->info("Purged personal data for {$purgedCount} organizer(s).");

        return self::SUCCESS;
    }
}
