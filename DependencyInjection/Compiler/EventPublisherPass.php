<?php

declare(strict_types=1);


namespace AFS\ProophBundle\DependencyInjection\Compiler;


use AFS\ProophBundle\Prooph\EventPublisher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class EventPublisherPass implements CompilerPassInterface
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $stores = $container->getParameter('prooph_event_store.stores');

        $defs = [];

        foreach ($stores as $store) {
            $def = new Definition();
            $def->setClass(EventPublisher::class);
            $def->addTag('prooph_event_store.'.$store.'.plugin');

            $defs['prooph_event_store.'.$store.'.event_publisher'] = $def;
        }

        $container->addDefinitions($defs);
    }

}