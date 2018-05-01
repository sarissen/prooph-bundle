<?php

declare(strict_types=1);


namespace AFS\ProophBundle\DependencyInjection\Compiler;


use AFS\ProophBundle\Prooph\Command\CreateProjectionTableCommand;
use AFS\ProophBundle\Prooph\Command\CreateStreamTableCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterCommandsPass implements CompilerPassInterface
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $connection = $container->getDefinition('doctrine.dbal.default_connection');

        if(!$connection){
            throw new \RuntimeException('Missing doctrine connection.');
        }

        $projectionCommand = new Definition(CreateProjectionTableCommand::class, [$connection]);
        $projectionCommand->addTag('console.command');

        $eventStreamCommand = new Definition(CreateStreamTableCommand::class, [$connection]);
        $eventStreamCommand->addTag('console.command');

        $container->addDefinitions([$projectionCommand, $eventStreamCommand]);
    }

}