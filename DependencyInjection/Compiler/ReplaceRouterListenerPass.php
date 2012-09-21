<?php

namespace Butterweed\SF1EmbedderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReplaceRouterListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // disable default router listener
        $router = $container->getDefinition('router_listener');
        $tags = $router->getTags();
        unset($tags['kernel.event_subscriber']);
        $router->setTags($tags);

        // TODO hook into profiler time and memory panel for measuring overhead
    }
}