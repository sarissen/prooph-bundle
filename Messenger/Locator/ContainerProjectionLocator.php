<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Messenger\Locator;



use Psr\Container\ContainerInterface;

class ContainerProjectionLocator implements ProjectionLocatorInterface
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolve($message): ?callable
    {
        $messageClass = \get_class($message);
        $projectorKey = 'projector.'.$messageClass;

        if (!$this->container->has($projectorKey)) {
            return null;
        }

        return $this->container->get($projectorKey);
    }

}