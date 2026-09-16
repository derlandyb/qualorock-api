<?php

namespace Tests\Unit\Policies;

use App\Application\Policies\SuperAdminOrganizerPolicy;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\SuperAdmin;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class SuperAdminOrganizerPolicyTest extends TestCase
{
    private SuperAdminOrganizerPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new SuperAdminOrganizerPolicy;
    }

    #[Test]
    #[TestDox('GIVEN a super admin WHEN checking any action THEN it is authorized')]
    public function it_authorizes_a_super_admin(): void
    {
        $superAdmin = new SuperAdmin;

        $this->assertTrue($this->policy->viewPending($superAdmin));
        $this->assertTrue($this->policy->approve($superAdmin));
        $this->assertTrue($this->policy->reject($superAdmin));
    }

    #[Test]
    #[TestDox('GIVEN an organizer WHEN checking any action THEN it is denied')]
    public function it_denies_an_organizer(): void
    {
        $organizer = new Organizer;

        $this->assertFalse($this->policy->viewPending($organizer));
        $this->assertFalse($this->policy->approve($organizer));
        $this->assertFalse($this->policy->reject($organizer));
    }

    #[Test]
    #[TestDox('GIVEN no authenticated user WHEN checking any action THEN it is denied')]
    public function it_denies_an_unauthenticated_request(): void
    {
        $this->assertFalse($this->policy->viewPending(null));
        $this->assertFalse($this->policy->approve(null));
        $this->assertFalse($this->policy->reject(null));
    }
}
