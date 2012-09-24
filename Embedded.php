<?php

namespace Butterweed\SF1EmbedderBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Butterweed\SF1EmbedderBundle\Event\ContextEvent;

class Embedded 
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \sfWebResponse
     * @throws \sfError404Exception
     * @throws \sfException
     */
    public function serve($app, $root)
    {
        $container = $this->container;
        $kernel = $container->get('kernel');
        $dispatcher = $container->get('event_dispatcher');

        define('SF2_EMBEDDED', true);
        $session_id = $container->get('session')->getId();

        require_once rtrim($root, '/ ').'/config/ProjectConfiguration.class.php';

        $configuration = \ProjectConfiguration::getApplicationConfiguration($app, $kernel->getEnvironment(), $kernel->isDebug());
        $dispatcher->dispatch('butterweed_sf1_embedder.pre_context');
        $context = \sfContext::createInstance($configuration);
        $eventContext = new ContextEvent($context);
        $dispatcher->dispatch('butterweed_sf1_embedder.pre_dispatch', $eventContext);
        $context->getController()->dispatch();
        $dispatcher->dispatch('butterweed_sf1_embedder.post_dispatch', $eventContext);

        // especially needed for response listeners such as debug bar
        $response = $context->getResponse();
        $event = $context->getEventDispatcher()->filter(new \sfEvent($response, 'response.filter_content'), $response->getContent());
        $response->setContent($event->getReturnValue());

        return $response;
    }
}