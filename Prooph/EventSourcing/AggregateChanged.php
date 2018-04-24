<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing;


use Prooph\EventSourcing\AggregateChanged as OriginalAggregateChanged;

class AggregateChanged extends OriginalAggregateChanged
{

    public static function occur(string $aggregateId, array $payload = [], array $metadata = []): OriginalAggregateChanged
    {
        return new static($aggregateId, $payload, $metadata);
    }

    protected function __construct(string $aggregateId, array $payload, array $metadata = [])
    {
        //Metadata needs to be set before setAggregateId and setVersion is called
        $this->metadata = $metadata;
        $this->setAggregateId($aggregateId);
        $this->setVersion($metadata['_aggregate_version'] ?? 1);
        $this->setMessageName($metadata['message_name'] ?? get_class($this));
        $this->setPayload($payload);
        $this->init();
    }

    protected function setMessageName(string $messageName)
    {
        $this->messageName = $messageName;
    }

}