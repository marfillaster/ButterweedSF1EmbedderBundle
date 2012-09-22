<?php

namespace Butterweed\SF1EmbedderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ReplaceRouterListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // disable default router listener
        $router = $container->getDefinition('router_listener');
        $tags = $router->getTags();
        unset($tags['kernel.event_subscriber']);
        $router->setTags($tags);

        $container->getDefinition('event_dispatcher')
            ->addMethodCall('addSubscriber', array(new Reference('butterweed_sf1_embedder.session_subscriber')));
        // TODO hook into profiler time and memory panel for measuring overhead
    }
}