<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing;


use AFS\ProophBundle\Prooph\EventSourcing\Aggregate\EventProducingAggregateInterface;
use AFS\ProophBundle\Prooph\EventSourcing\Aggregate\EventSourcedAggregateInterface;
use AFS\ProophBundle\Prooph\EventSourcing\Converter\AggregateChangedConverterInterface;
use Iterator;
use Prooph\Common\Messaging\Message;
use Prooph\EventSourcing\Aggregate\AggregateTranslator as BaseTranslator;
use Prooph\EventSourcing\Aggregate\AggregateType;
use RuntimeException;

class AggregateTranslator implements BaseTranslator
{

    /**
     * @var AggregateChangedConverterInterface
     */
    private $converter;


    /**
     * AggregateTranslator constructor.
     * @param AggregateChangedConverterInterface $converter
     */
    public function __construct(AggregateChangedConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param EventSourcedAggregateInterface|EventProducingAggregateInterface $eventSourcedAggregateRoot
     *
     * @return int
     */
    public function extractAggregateVersion($eventSourcedAggregateRoot): int
    {
        return $eventSourcedAggregateRoot->getVersion();
    }

    /**
     * @param EventSourcedAggregateInterface|EventProducingAggregateInterface $anEventSourcedAggregateRoot
     *
     * @return string
     */
    public function extractAggregateId($anEventSourcedAggregateRoot): string
    {
        return $anEventSourcedAggregateRoot->aggregateId();
    }

    /**
     * @param AggregateType $aggregateType
     * @param Iterator $historyEvents
     *
     * @return mixed reconstructed AggregateRoot
     */
    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, Iterator $historyEvents)
    {
        if (! $aggregateRootClass = $aggregateType->mappedClass()) {
            $aggregateRootClass = $aggregateType->toString();
        }

        if (! class_exists($aggregateRootClass) || !is_subclass_of($aggregateRootClass, EventSourcedAggregateInterface::class)) {
            throw new RuntimeException(
                sprintf('Aggregate root class %s cannot be found', $aggregateRootClass)
            );
        }

        $aggregateRootClass::reconstituteFromHistory($historyEvents);
    }

    /**
     * @param EventProducingAggregateInterface $anEventSourcedAggregateRoot
     *
     * @return Message[]
     */
    public function extractPendingStreamEvents($anEventSourcedAggregateRoot): array
    {
        $pendingEvents = $anEventSourcedAggregateRoot->popRecordedEvents();
        return $this->converter->toAggregateChangedArray($this->extractAggregateId($anEventSourcedAggregateRoot), $pendingEvents);
    }

    /**
     * @param EventSourcedAggregateInterface $anEventSourcedAggregateRoot
     * @param Iterator $events
     *
     * @return void
     */
    public function replayStreamEvents($anEventSourcedAggregateRoot, Iterator $events): void
    {
        $anEventSourcedAggregateRoot->replay($events);
    }

}