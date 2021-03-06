<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing;


class VersionedEvent
{

    /**
     * @var mixed
     */
    private $event;

    /**
     * @var integer
     */
    private $version;


    public function __construct(object $event, int $version)
    {
        $this->event = $event;
        $this->version = $version;
    }

    public function event(): object
    {
        return $this->event;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function messageName(): string
    {
        return get_class($this->event);
    }

}