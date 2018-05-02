<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Messenger\Locator;


interface ProjectionLocatorInterface
{
    /**
     * Returns the projection for the given message.
     *
     * @param object $message
     *
     * @throws NoHandlerForMessageException
     *
     * @return callable
     */
    public function resolve($message): callable;
}