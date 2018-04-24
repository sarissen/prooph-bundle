<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventStore\Plugin;


use AFS\BusBundle\Messenger\Bus\EventBusInterface;
use AFS\ProophBundle\Prooph\EventSourcing\Converter\AggregateChangedConverterInterface;
use Prooph\Common\Event\ActionEvent;
use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\Plugin\AbstractPlugin;

final class EventConverterPlugin extends AbstractPlugin
{

    /**
     * @var EventBusInterface
     */
    private $eventBus;

    /**
     * @var \Iterator[]
     */
    private $cachedEventStreams = [];

    /**
     * @var AggregateChangedConverterInterface
     */
    private $converter;

    public function __construct(EventBusInterface $eventBus, AggregateChangedConverterInterface $converter)
    {
        $this->eventBus = $eventBus;
        $this->converter = $converter;
    }

    /**
     * @inheritdoc
     */
    public function attachToEventStore(ActionEventEmitterEventStore $eventStore): void
    {
        $this->listenerHandlers[] = $eventStore->attach(
            ActionEventEmitterEventStore::EVENT_LOAD,
            function (ActionEvent $event) use ($eventStore): void {
                $loadedEvents = $event->getParam('streamEvents', new \ArrayIterator());

                if ($event->getParam('streamNotFound', false)) {
                    return;
                }

                $event->setParam('streamEvents', $this->converter->fromAggregateChangedStream($loadedEvents));
            },
            PluginPriorities::CONVERT_LOAD
        );
    }

}