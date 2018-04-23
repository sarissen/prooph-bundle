<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\Converter;


use AFS\ProophBundle\Prooph\EventSourcing\Aggregate\VersionedEvent;
use AFS\ProophBundle\Prooph\EventSourcing\AggregateChanged;

interface AggregateChangedConverterInterface
{

    /**
     * @param string $aggregateId
     * @param array $events
     * @return AggregateChanged[]
     */
    public function toAggregateChangedArray(string $aggregateId, array $events): array;

    /**
     * @param \Iterator $proophEvents
     * @return \Iterator
     */
    public function fromAggregateChangedStream(\Iterator $proophEvents): \Iterator;

    /**
     * @param string $aggregateId
     * @param VersionedEvent $event
     * @return AggregateChanged
     */
    public function toAggregateChanged(string $aggregateId, VersionedEvent $event): AggregateChanged;

    /**
     * @param AggregateChanged $proophEvent
     * @return VersionedEvent
     */
    public function fromAggregateChanged(AggregateChanged $proophEvent): VersionedEvent;

}