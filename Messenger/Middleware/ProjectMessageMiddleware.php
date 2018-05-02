<?php

declare(strict_types=1);

namespace AFS\ProophBundle\Messenger\Middleware;

use AFS\ProophBundle\Messenger\Locator\ProjectionLocatorInterface;
use Symfony\Component\Messenger\MiddlewareInterface;

class ProjectMessageMiddleware implements MiddlewareInterface
{

    private $messageProjectorLocator;

    public function __construct(ProjectionLocatorInterface $messageProjectorLocator)
    {
        $this->messageProjectorLocator = $messageProjectorLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        $projector = $this->messageProjectorLocator->resolve($message);
        $result = $projector($message);

        $next($message);

        return $result;
    }

}