<?php

declare(strict_types=1);


namespace AFS\ProophBundle;


use AFS\ProophBundle\DependencyInjection\Compiler\OverrideProjectionManagerFactoryPass;
use AFS\ProophBundle\DependencyInjection\Compiler\RegisterCommandsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AFSProophBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideProjectionManagerFactoryPass());
        $container->addCompilerPass(new RegisterCommandsPass());
    }

}