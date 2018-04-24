<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing\Aggregate;


use AFS\ProophBundle\Prooph\EventStore\Plugin\EventSourcing\VersionedEvent;

trait EventProducerTrait
{
    /**
     * Current version
     *
     * @var int
     */
    protected $version = 0;

    /**
     * List of events that are not committed to the EventStore
     *
     * @var array
     */
    protected $recordedEvents = [];

    /**
     * Get pending events and reset stack
     *
     * @return VersionedEvent[]
     */
    protected function popRecordedEvents(): array
    {
        $pendingEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        $changeEvents = [];

        foreach ($pendingEvents as $event){
            $this->version += 1;
            $versionedEvent = new VersionedEvent($event, $this->version);
            $changeEvents[] = $versionedEvent;
        }

        return $changeEvents;
    }

    /**
     * Record an aggregate changed event
     */
    protected function recordThat($event): void
    {
        $this->recordedEvents[] = $event;

        $this->apply($event);
    }

    abstract protected function aggregateId(): string;

    /**
     * Apply given event
     */
    abstract protected function apply($event): void;
}