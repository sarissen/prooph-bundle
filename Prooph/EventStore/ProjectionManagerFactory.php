<?php

declare(strict_types=1);

namespace AFS\ProophBundle\Prooph\EventStore;

use PDO;
use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\EventStore;
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
        string $projectionsTable = 'projections'
    ): ProjectionManager {
        $checkConnection = function () use ($connection) {
            if (! $connection instanceof PDO) {
                throw new RuntimeException('PDO connection missing');
            }
        };

        if ($eventStore instanceof InMemoryEventStore) {
            return new InMemoryProjectionManager($eventStore);
        }

        if ($eventStore instanceof PostgresEventStore) {
            $checkConnection();

            return new PostgresProjectionManager($eventStore, $connection, $eventStreamsTable, $projectionsTable);
        }

        if ($eventStore instanceof MySqlEventStore) {
            $checkConnection();

            return new MySqlProjectionManager($eventStore, $connection, $eventStreamsTable, $projectionsTable);
        }

        if ($eventStore instanceof MariaDbEventStore) {
            $checkConnection();

            return new MariaDbProjectionManager($eventStore, $connection, $eventStreamsTable, $projectionsTable);
        }

        if($eventStore instanceof ActionEventEmitterEventStore){
            return $this->createProjectionManager($eventStore->getInnerEventStore());
        }

        throw new RuntimeException(sprintf('ProjectionManager for %s not implemented.', get_class($eventStore)));
    }
}