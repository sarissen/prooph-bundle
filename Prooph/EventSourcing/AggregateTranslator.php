<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing;


use AFS\ProophBundle\Prooph\EventSourcing\Aggregate\AbstractEntityDecorator;
use AFS\ProophBundle\Prooph\EventSourcing\Converter\AggregateChangedConverterInterface;
use Iterator;
use Prooph\Common\Messaging\Message;
use Prooph\EventSourcing\Aggregate\AggregateTranslator as BaseTranslator;
use Prooph\EventSourcing\Aggregate\AggregateType;

class AggregateTranslator implements BaseTranslator
{

    /**
     * @var AbstractEntityDecorator
     */
    protected $aggregateRootDecorator;

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
     * @param mixed $eventSourcedAggregateRoot
     *
     * @return int
     */
    public function extractAggregateVersion($eventSourcedAggregateRoot): int
    {
        return $this->getAggregateRootDecorator()->extractAggregateVersion($eventSourcedAggregateRoot);
    }

    /**
     * @param mixed $anEventSourcedAggregateRoot
     *
     * @return string
     */
    public function extractAggregateId($anEventSourcedAggregateRoot): string
    {
        return $this->getAggregateRootDecorator()->extractAggregateId($anEventSourcedAggregateRoot);
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

        return $this->getAggregateRootDecorator()
            ->fromHistory($aggregateRootClass, $historyEvents);
    }

    /**
     * @param mixed $anEventSourcedAggregateRoot
     *
     * @return Message[]
     */
    public function extractPendingStreamEvents($anEventSourcedAggregateRoot): array
    {
        $pendingEvents = $this->getAggregateRootDecorator()->extractRecordedEvents($anEventSourcedAggregateRoot);
        return $this->converter->toAggregateChangedArray($this->extractAggregateId($anEventSourcedAggregateRoot), $pendingEvents);
    }

    /**
     * @param mixed $anEventSourcedAggregateRoot
     * @param Iterator $events
     *
     * @return void
     */
    public function replayStreamEvents($anEventSourcedAggregateRoot, Iterator $events): void
    {
        $this->getAggregateRootDecorator()->replayStreamEvents($anEventSourcedAggregateRoot, $events);
    }

    /**
     * @return AbstractEntityDecorator
     */
    public function getAggregateRootDecorator(): AbstractEntityDecorator
    {
        if (null === $this->aggregateRootDecorator) {
            $this->aggregateRootDecorator = AbstractEntityDecorator::newInstance();
        }

        return $this->aggregateRootDecorator;
    }

    /**
     * @param AbstractEntityDecorator $anAggregateRootDecorator
     */
    public function setAggregateRootDecorator(AbstractEntityDecorator $anAggregateRootDecorator): void
    {
        $this->aggregateRootDecorator = $anAggregateRootDecorator;
    }

}