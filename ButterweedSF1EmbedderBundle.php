<?php

namespace Butterweed\SF1EmbedderBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Butterweed\SF1EmbedderBundle\DependencyInjection\Compiler\ReplaceRouterListenerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ButterweedSF1EmbedderBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ReplaceRouterListenerPass());
    }
}