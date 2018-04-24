<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventStore\Plugin;


use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\Plugin\AbstractPlugin;

final class ProjectionPlugin extends AbstractPlugin
{

    /**
     * @inheritdoc
     */
    public function attachToEventStore(ActionEventEmitterEventStore $eventStore): void
    {
        // TODO: Implement attachToEventStore() method.
    }

}