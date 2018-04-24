<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing\Converter;


use AFS\ProophBundle\Prooph\EventSourcing\AggregateChanged;
use AFS\ProophBundle\Prooph\EventSourcing\VersionedEvent;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AggregateChangedConverter implements AggregateChangedConverterInterface
{

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;


    public function __construct(NormalizerInterface $normalizer, DenormalizerInterface $denormalizer)
    {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
    }

    public function toAggregateChangedArray(string $aggregateId, array $events): array
    {
        $proophEvents = [];

        foreach ($events as $event) {
            if(!$event instanceof AggregateChanged){
                $proophEvents[] = $this->toAggregateChanged($aggregateId, $event);
            }
        }

        return $proophEvents;
    }

    public function fromAggregateChangedStream(\Iterator $proophEvents): \Iterator
    {
        $events = new \ArrayIterator();

        foreach ($proophEvents as $key => $proophEvent)
        {
            $events[$key] = $proophEvent;
        }

        return $events;
    }

    /**
     * @param string $aggregateId
     * @param VersionedEvent $event
     * @return AggregateChanged
     */
    public function toAggregateChanged(string $aggregateId, VersionedEvent $event): AggregateChanged
    {
        if(!$event instanceof AggregateChanged){
            $proophEvent = AggregateChanged::occur($aggregateId, $this->normalizer->normalize($event->event()), ['message_name' => get_class($event->event())]);
            $proophEvent->withVersion($event->version());
        }

        return $proophEvent;
    }

    /**
     * @param AggregateChanged $proophEvent
     * @return VersionedEvent
     */
    public function fromAggregateChanged(AggregateChanged $proophEvent): VersionedEvent
    {
        return new VersionedEvent($this->denormalizer->denormalize($proophEvent->payload(), $proophEvent->messageName()), $proophEvent->version());
    }
}