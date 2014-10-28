<?php

namespace Butterweed\SF1EmbedderBundle\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class Router implements RouterInterface, ContainerAwareInterface
{
    protected $container, $context, $map, $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function setMap($map)
    {
        $this->map = $map;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        $params = array('_controller' => 'ButterweedSF1EmbedderBundle:Default:index', '_route' => 'butterweed_sf1_embedder_default_index');
        $request = $this->container->get('request');

        foreach ($this->map as $conf) {
            if (0 === strpos($request->getPathInfo(), $conf['prefix']) && ($conf['hosts'] && in_array($request->getHost(), $conf['hosts']))) {
                if (null !== $this->logger) {
                    $this->logger->info(sprintf('Matched embedded symfony (%s)', $conf['app']));
                }
                
                return array_merge($params, array_diff_key($conf, array('hosts'=>null)));
            }
        }

        throw new ResourceNotFoundException;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        return new RouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        throw new RouteNotFoundException;
    }
}