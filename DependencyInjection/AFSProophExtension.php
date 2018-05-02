<?php

declare(strict_types=1);


namespace AFS\ProophBundle\DependencyInjection;


use AFS\ProophBundle\Messenger\MessageProjectorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AFSProophExtension extends Extension
{

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(MessageProjectorInterface::class)
            ->addTag('messenger.message_projector');
    }

}