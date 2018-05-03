<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing\Aggregate;

use AFS\ProophBundle\Prooph\EventSourcing\VersionedEvent;
use Iterator;


/**
 * Base aggregate root class
 */
abstract class AggregateRoot implements EventProducingAggregateInterface, EventSourcedAggregateInterface
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
     * @inheritdoc
     */
    abstract public function aggregateId(): string;

    /**
     * @inheritdoc
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public static function reconstituteFromHistory(Iterator $historyEvents)
    {
        $instance = new static();
        $instance->replay($historyEvents);

        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function replay(Iterator $historyEvents): void
    {
        foreach ($historyEvents as $pastEvent) {
            /** @var VersionedEvent $pastEvent */
            $this->version = $pastEvent->version();

            $this->apply($pastEvent->event());
        }
    }

    /**
     * @inheritdoc
     */
    public function popRecordedEvents(): array
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
     * Store and apply Domain Event
     */
    protected function recordThat($event): void
    {
        $this->recordedEvents[] = $event;

        $this->apply($event);
    }

    /**
     * Apply Domain Event
     */
    protected function apply($e): void
    {
        $handler = $this->determineEventHandlerMethodFor($e);

        if (! method_exists($this, $handler)) {
            throw new \RuntimeException(sprintf(
                'Missing event handler method %s for aggregate root %s',
                $handler,
                get_class($this)
            ));
        }

        $this->{$handler}($e);
    }

    /**
     * Determine apply function in entity, e.g. for OrderWasReceived event: whenOrderWasReceived
     */
    protected function determineEventHandlerMethodFor($e): string
    {
        return 'when' . implode(array_slice(explode('\\', get_class($e)), -1));
    }

}