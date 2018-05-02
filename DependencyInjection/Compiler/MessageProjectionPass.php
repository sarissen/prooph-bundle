<?php

declare(strict_types=1);


namespace AFS\ProophBundle\DependencyInjection\Compiler;


use AFS\ProophBundle\Messenger\ChainProjector;
use AFS\ProophBundle\Messenger\MessageProjectorInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class MessageProjectionPass implements CompilerPassInterface
{

    /**
     * @var string
     */
    private $messageBusService;
    /**
     * @var string
     */
    private $messageProjectionResolverService;
    /**
     * @var string
     */
    private $projectorTag;

    public function __construct(string $messageBusService = 'message_bus', string $messageProjectionResolverService = 'messenger.projection_resolver', string $projectorTag = 'messenger.message_projector')
    {
        $this->messageBusService = $messageBusService;
        $this->messageProjectionResolverService = $messageProjectionResolverService;
        $this->projectorTag = $projectorTag;
    }

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerProjectors($container);
    }

    private function registerProjectors(ContainerBuilder $container)
    {
        $projectorsByMessage = array();

        foreach ($container->findTaggedServiceIds($this->projectorTag, true) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $projects = isset($tag['projects']) ? array($tag['projects']) : $this->guessProjectedClasses($r = $container->getReflectionClass($container->getDefinition($serviceId)->getClass()), $serviceId);
                $priority = $tag['priority'] ?? 0;

                foreach ($projects as $messageClass) {
                    if (\is_array($messageClass)) {
                        $messagePriority = $messageClass[1];
                        $messageClass = $messageClass[0];
                    } else {
                        $messagePriority = $priority;
                    }

                    if (!class_exists($messageClass)) {
                        $messageClassLocation = isset($tag['projects']) ? 'declared in your tag attribute "projects"' : sprintf($r->implementsInterface(MessageProjectorInterface::class) ? 'returned by method "%s::getProjectedMessages()"' : 'used as argument type in method "%s::__invoke()"', $r->getName());

                        throw new RuntimeException(sprintf('Invalid projector service "%s": message class "%s" %s does not exist.', $serviceId, $messageClass, $messageClassLocation));
                    }

                    $projectorsByMessage[$messageClass][$messagePriority][] = new Reference($serviceId);
                }
            }
        }

        foreach ($projectorsByMessage as $message => $projectors) {
            krsort($projectorsByMessage[$message]);
            $projectorsByMessage[$message] = \call_user_func_array('array_merge', $projectorsByMessage[$message]);
        }

        $definitions = array();
        foreach ($projectorsByMessage as $message => $projectors) {
            if (1 === \count($projectors)) {
                $handlersByMessage[$message] = current($projectors);
            } else {
                $d = new Definition(ChainProjector::class, array($projectors));
                $d->setPrivate(true);
                $serviceId = hash('sha1', $message);
                $definitions[$serviceId] = $d;
                $projectorsByMessage[$message] = new Reference($serviceId);
            }
        }
        $container->addDefinitions($definitions);

        $projectorsLocatorMapping = array();
        foreach ($projectorsByMessage as $message => $projector) {
            $projectorsLocatorMapping['projector.'.$message] = $projector;
        }

        $projectorResolver = $container->getDefinition($this->messageProjectionResolverService);
        $projectorResolver->replaceArgument(0, ServiceLocatorTagPass::register($container, $projectorsLocatorMapping));
    }

    private function guessProjectedClasses(\ReflectionClass $projectorClass, string $serviceId): array
    {
        if ($projectorClass->implementsInterface(MessageProjectorInterface::class)) {
            if (!$projectedMessages = $projectorClass->getName()::getProjectedMessages()) {
                throw new RuntimeException(sprintf('Invalid projector service "%s": method "%s::getProjectedMessages()" must return one or more messages.', $serviceId, $projectorClass->getName()));
            }

            return $projectedMessages;
        }

        throw new RuntimeException(sprintf('Invalid projector service "%s": must implement MessageProjectorInterface.', $serviceId));
    }

}