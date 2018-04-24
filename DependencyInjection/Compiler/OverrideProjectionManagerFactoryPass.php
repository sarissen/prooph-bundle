<?php

declare(strict_types=1);

namespace AFS\ProophBundle\DependencyInjection\Compiler;

use AFS\ProophBundle\Prooph\EventStore\ProjectionManagerFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideProjectionManagerFactoryPass implements CompilerPassInterface
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->getDefinition('prooph_event_store.projection_factory');
        $def->setClass(ProjectionManagerFactory::class);
    }

}