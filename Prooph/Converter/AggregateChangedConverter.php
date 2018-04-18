<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\Converter;


use AFS\ProophBundle\Prooph\EventSourcing\Aggregate\VersionedEvent;
use AFS\ProophBundle\Prooph\EventSourcing\AggregateChanged;
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

    public function toAggregateChanged(string $aggregateId, array $events): array
    {
        $proophEvents = [];

        foreach ($events as $event) {
            if(!$event instanceof AggregateChanged){
                $proophEvent = AggregateChanged::occur($aggregateId, $this->normalizer->normalize($event->event()), ['message_name' => get_class($event->event())]);
                $proophEvent->withVersion($event->version());
                $proophEvents[] = $proophEvent;
            }
        }

        return $proophEvents;
    }

    public function fromAggregateChanged(\Iterator $proophEvents): \Iterator
    {
        $events = new \ArrayIterator();

        foreach ($proophEvents as $proophEvent)
        {
            $event = new VersionedEvent($this->denormalizer->denormalize($proophEvent->payload(), $proophEvent->messageName()), $proophEvent->version());

            $events->append($event);
        }

        return $events;
    }

}