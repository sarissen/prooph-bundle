<?php

declare(strict_types=1);

namespace AFS\ProophBundle\Prooph\EventStore;

use PDO;
use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\EventStoreDecorator;
use Prooph\EventStore\InMemoryEventStore;
use Prooph\EventStore\Pdo\MariaDbEventStore;
use Prooph\EventStore\Pdo\MySqlEventStore;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\EventStore\Pdo\Projection\MariaDbProjectionManager;
use Prooph\EventStore\Pdo\Projection\MySqlProjectionManager;
use Prooph\EventStore\Pdo\Projection\PostgresProjectionManager;
use Prooph\EventStore\Projection\InMemoryProjectionManager;
use Prooph\EventStore\Projection\ProjectionManager;
use RuntimeException;

class ProjectionManagerFactory
{
    public function createProjectionManager(
        EventStore $eventStore,
        ?PDO $connection = null,
        string $eventStreamsTable = 'event_streams',
        string $projectionsTable = 'projections',
        EventStore $originalEventStore = null
    ): ProjectionManager {
        $checkConnection = function () use ($connection) {
            if (! $connection instanceof PDO) {
                throw new RuntimeException('PDO connection missing');
            }
        };

        if ($eventStore instanceof InMemoryEventStore) {
            return new InMemoryProjectionManager($originalEventStore ?? $eventStore);
        }

        if ($eventStore instanceof PostgresEventStore) {
            $checkConnection();

            return new PostgresProjectionManager($originalEventStore ?? $eventStore, $connection, $eventStreamsTable, $projectionsTable);
        }

        if ($eventStore instanceof MySqlEventStore) {
            $checkConnection();

            return new MySqlProjectionManager($originalEventStore ?? $eventStore, $connection, $eventStreamsTable, $projectionsTable);
        }

        if ($eventStore instanceof MariaDbEventStore) {
            $checkConnection();

            return new MariaDbProjectionManager($originalEventStore ?? $eventStore, $connection, $eventStreamsTable, $projectionsTable);
        }

        if($eventStore instanceof EventStoreDecorator){
            return $this->createProjectionManager($eventStore->getInnerEventStore(), $connection, $eventStreamsTable, $projectionsTable, $eventStore);
        }

        throw new RuntimeException(sprintf('ProjectionManager for %s not implemented.', get_class($eventStore)));
    }
}