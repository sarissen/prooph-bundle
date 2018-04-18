<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing;


use AFS\ProophBundle\Prooph\Converter\AggregateChangedConverterInterface;
use AFS\ProophBundle\Prooph\EventSourcing\Aggregate\AbstractEntityDecorator;
use Iterator;
use Prooph\Common\Messaging\Message;
use Prooph\EventSourcing\Aggregate\AggregateTranslator as BaseTranslator;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

        $convertedEvents = $this->converter->fromAggregateChanged($historyEvents);

        return $this->getAggregateRootDecorator()
            ->fromHistory($aggregateRootClass, $convertedEvents);
    }

    /**
     * @param mixed $anEventSourcedAggregateRoot
     *
     * @return Message[]
     */
    public function extractPendingStreamEvents($anEventSourcedAggregateRoot): array
    {
        $pendingEvents = $this->getAggregateRootDecorator()->extractRecordedEvents($anEventSourcedAggregateRoot);
        return $this->converter->toAggregateChanged($this->extractAggregateId($anEventSourcedAggregateRoot), $pendingEvents);
    }

    /**
     * @param mixed $anEventSourcedAggregateRoot
     * @param Iterator $events
     *
     * @return void
     */
    public function replayStreamEvents($anEventSourcedAggregateRoot, Iterator $events): void
    {
        $convertedEvents = $this->converter->fromAggregateChanged($events);
        $this->getAggregateRootDecorator()->replayStreamEvents($anEventSourcedAggregateRoot, $convertedEvents);
    }

    public function getAggregateRootDecorator(): AbstractEntityDecorator
    {
        if (null === $this->aggregateRootDecorator) {
            $this->aggregateRootDecorator = AbstractEntityDecorator::newInstance();
        }

        return $this->aggregateRootDecorator;
    }

    public function setAggregateRootDecorator(AbstractEntityDecorator $anAggregateRootDecorator): void
    {
        $this->aggregateRootDecorator = $anAggregateRootDecorator;
    }

}