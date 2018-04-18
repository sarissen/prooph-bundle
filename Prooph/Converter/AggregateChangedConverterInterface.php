<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\Converter;


interface AggregateChangedConverterInterface
{

    public function toAggregateChanged(string $aggregateId, array $events): array;

    public function fromAggregateChanged(\Iterator $proophEvents): \Iterator;

}