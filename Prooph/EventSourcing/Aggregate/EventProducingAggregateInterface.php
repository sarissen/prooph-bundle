<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing\Aggregate;


interface EventProducingAggregateInterface
{

    public function popRecordedEvents(): array;

    public function aggregateId(): string;

    public function getVersion(): int;

}