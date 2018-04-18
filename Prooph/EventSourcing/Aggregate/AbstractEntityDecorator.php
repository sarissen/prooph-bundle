<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing\Aggregate;

use Iterator;
use RuntimeException;
use BadMethodCallException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AbstractEntityDecorator extends AbstractEntity
{

    public static function newInstance(): self
    {
        return new static();
    }

    public function extractAggregateVersion(AbstractEntity $anAggregateRoot): int
    {
        return $anAggregateRoot->version;
    }

    /**
     * @param AbstractEntity $anAggregateRoot
     *
     * @return VersionedEvent[]
     */
    public function extractRecordedEvents(AbstractEntity $anAggregateRoot): array
    {
        return $anAggregateRoot->popRecordedEvents();
    }

    public function extractAggregateId(AbstractEntity $anAggregateRoot): string
    {
        return $anAggregateRoot->aggregateId();
    }

    /**
     * @throws RuntimeException
     */
    public function fromHistory($arClass, Iterator $aggregateChangedEvents): AbstractEntity
    {
        if (! class_exists($arClass)) {
            throw new RuntimeException(
                sprintf('Aggregate root class %s cannot be found', $arClass)
            );
        }

        return $arClass::reconstituteFromHistory($aggregateChangedEvents);
    }

    public function replayStreamEvents(AbstractEntity $aggregateRoot, Iterator $events): void
    {
        $aggregateRoot->replay($events);
    }

    /**
     * @throws BadMethodCallException
     */
    protected function aggregateId(): string
    {
        throw new BadMethodCallException('The AggregateRootDecorator does not have an id');
    }

    protected function apply($e): void
    {
    }
}