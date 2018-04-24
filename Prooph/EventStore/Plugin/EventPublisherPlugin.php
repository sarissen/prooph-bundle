<?php

declare(strict_types=1);

namespace AFS\ProophBundle\Prooph\EventStore\Plugin;

use AFS\BusBundle\Messenger\Bus\EventBusInterface;
use AFS\ProophBundle\Prooph\EventSourcing\Converter\AggregateChangedConverterInterface;
use Prooph\Common\Event\ActionEvent;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Plugin\AbstractPlugin;
use Prooph\EventStore\TransactionalActionEventEmitterEventStore;

final class EventPublisherPlugin extends AbstractPlugin
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

    public function attachToEventStore(ActionEventEmitterEventStore $eventStore): void
    {
        $this->listenerHandlers[] = $eventStore->attach(
            ActionEventEmitterEventStore::EVENT_APPEND_TO,
            function (ActionEvent $event) use ($eventStore): void {
                $recordedEvents = $event->getParam('streamEvents', new \ArrayIterator());

                if (! $this->inTransaction($eventStore)) {
                    if ($event->getParam('streamNotFound', false)
                        || $event->getParam('concurrencyException', false)
                    ) {
                        return;
                    }

                    foreach ($recordedEvents as $recordedEvent) {
                        $this->eventBus->dispatch($this->constructEvent($recordedEvent));
                    }
                } else {
                    $this->cachedEventStreams[] = $recordedEvents;
                }
            },
            PluginPriorities::PUBLISH
        );

        $this->listenerHandlers[] = $eventStore->attach(
            ActionEventEmitterEventStore::EVENT_CREATE,
            function (ActionEvent $event) use ($eventStore): void {
                $stream = $event->getParam('stream');
                $recordedEvents = $stream->streamEvents();

                if (! $this->inTransaction($eventStore)) {
                    if ($event->getParam('streamExistsAlready', false)) {
                        return;
                    }

                    foreach ($recordedEvents as $recordedEvent) {
                        $this->eventBus->dispatch($this->constructEvent($recordedEvent));
                    }
                } else {
                    $this->cachedEventStreams[] = $recordedEvents;
                }
            },
            PluginPriorities::PUBLISH
        );

        if ($eventStore instanceof TransactionalActionEventEmitterEventStore) {
            $this->listenerHandlers[] = $eventStore->attach(
                TransactionalActionEventEmitterEventStore::EVENT_COMMIT,
                function (ActionEvent $event): void {
                    foreach ($this->cachedEventStreams as $stream) {
                        foreach ($stream as $recordedEvent) {
                            $this->eventBus->dispatch($this->constructEvent($recordedEvent));
                        }
                    }
                    $this->cachedEventStreams = [];
                },
                PluginPriorities::PUBLISH
            );

            $this->listenerHandlers[] = $eventStore->attach(
                TransactionalActionEventEmitterEventStore::EVENT_ROLLBACK,
                function (ActionEvent $event): void {
                    $this->cachedEventStreams = [];
                },
                PluginPriorities::PUBLISH
            );
        }
    }

    /**
     * @param EventStore $eventStore
     * @return bool
     */
    private function inTransaction(EventStore $eventStore): bool
    {
        return $eventStore instanceof TransactionalActionEventEmitterEventStore
            && $eventStore->inTransaction();
    }

    /**
     * @param AggregateChanged $event
     * @return mixed
     */
    private function constructEvent(AggregateChanged $event)
    {
        $events = new \ArrayIterator();
        $events->append($event);
        $convertedEvents = $this->converter->fromAggregateChangedStream($events);
        $convertedEvents->rewind();
        return $convertedEvents->current()->event();
    }

}