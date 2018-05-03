<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing\Aggregate;


interface EventSourcedAggregateInterface
{

    public static function reconstituteFromHistory(\Iterator $historyEvents);

    public function replay(\Iterator $historyEvents);

    public function aggregateId(): string;

    public function getVersion(): int;

}