<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing\Aggregate;


use Iterator;

trait EventSourcedTrait
{

    /**
     * Current version
     *
     * @var int
     */
    protected $version = 0;

    /**
     * @param Iterator $historyEvents
     * @return EventSourcedTrait
     */
    protected static function reconstituteFromHistory(Iterator $historyEvents)
    {
        $instance = new static();
        $instance->replay($historyEvents);

        return $instance;
    }

    /**
     * Replay past events
     *
     * @param Iterator $historyEvents
     */
    protected function replay(Iterator $historyEvents): void
    {
        foreach ($historyEvents as $pastEvent) {
            /** @var VersionedEvent $pastEvent */
            $this->version = $pastEvent->version();

            $this->apply($pastEvent->event());
        }
    }

    abstract protected function aggregateId(): string;

    /**
     * Apply given event
     */
    abstract protected function apply($event): void;

}