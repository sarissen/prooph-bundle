<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Messenger\Locator;


use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $handlerKey = 'handler.'.$messageClass;

        if (!$this->container->has($handlerKey)) {
            return null;
        }

        return $this->container->get($handlerKey);
    }

}