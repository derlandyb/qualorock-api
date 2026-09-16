<?php

namespace Tests\Unit\Services;

use App\Application\Services\PublishedEventCounter;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Doubles\FakeEventRepository;
use Tests\TestCase;

class PublishedEventCounterTest extends TestCase
{
    #[Test]
    #[TestDox('GIVEN 4 events published this calendar month WHEN counting THEN the service returns 4')]
    public function it_counts_events_published_this_calendar_month(): void
    {
        $repository = new FakeEventRepository;
        $now = new DateTimeImmutable('2026-03-15 12:00:00', new DateTimeZone('America/Sao_Paulo'));

        foreach ([1, 2, 3, 4] as $dayOfMonth) {
            $repository->addPublishedEvent(1, new DateTimeImmutable(sprintf('2026-03-%02d 10:00:00', $dayOfMonth), new DateTimeZone('America/Sao_Paulo')));
        }

        $counter = new PublishedEventCounter($repository);

        $this->assertSame(4, $counter->countForCurrentMonth(1, $now));
    }

    #[Test]
    #[TestDox('GIVEN an event published in the last minute of the month in America/Sao_Paulo but the next UTC day WHEN counting THEN it counts toward the correct month')]
    public function it_counts_a_month_boundary_event_by_the_organizers_local_calendar_month(): void
    {
        $repository = new FakeEventRepository;

        // 2026-01-31 23:59:00 America/Sao_Paulo == 2026-02-01 02:59:00 UTC.
        $lastMinuteOfJanuaryInSaoPaulo = new DateTimeImmutable('2026-01-31 23:59:00', new DateTimeZone('America/Sao_Paulo'));
        $repository->addPublishedEvent(1, $lastMinuteOfJanuaryInSaoPaulo);

        // Clearly inside February in Sao Paulo - must not be counted toward January.
        $earlyFebruaryInSaoPaulo = new DateTimeImmutable('2026-02-01 00:05:00', new DateTimeZone('America/Sao_Paulo'));
        $repository->addPublishedEvent(1, $earlyFebruaryInSaoPaulo);

        $counter = new PublishedEventCounter($repository);
        $referenceStillInJanuary = new DateTimeImmutable('2026-01-31 23:59:30', new DateTimeZone('America/Sao_Paulo'));

        $this->assertSame(1, $counter->countForCurrentMonth(1, $referenceStillInJanuary));
    }
}
