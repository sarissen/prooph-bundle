<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventSourcing\Aggregate;


/**
 * Contains generic date created/updated fields for logging/auditing purposes
 */
abstract class AbstractEntity
{

    use EventProducerTrait;
    use EventSourcedTrait;

    /**
     * @param mixed $e
     */
    protected function apply($e): void
    {
        $handler = $this->determineEventHandlerMethodFor($e);

        if (! method_exists($this, $handler)) {
            throw new \RuntimeException(sprintf(
                'Missing event handler method %s for aggregate root %s',
                $handler,
                get_class($this)
            ));
        }

        $this->{$handler}($e);
    }

    /**
     * @param mixed $e
     * @return string
     */
    protected function determineEventHandlerMethodFor($e): string
    {
        return 'when' . implode(array_slice(explode('\\', get_class($e)), -1));
    }

}