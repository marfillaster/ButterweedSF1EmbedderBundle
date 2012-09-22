<?php 

namespace Butterweed\SF1EmbedderBundle\EventListener;

use Symfony\Component\HttpKernel\EventListener\RouterListener as BaseRouterListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Butterweed\SF1EmbedderBundle\Event\ContextEvent;

class LegacyFallbackAwareRouterListener extends BaseRouterListener implements ContainerAwareInterface
{
    protected $container, $embbeds;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function setEmbbeds($embbeds)
    {
        $this->embbeds = $embbeds;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        try {
            parent::onKernelRequest($event);
        } catch (NotFoundHttpException $e) {
            $request = $event->getRequest();

            foreach ($this->embbeds as $prefix => $conf) {
                if (0 === strpos($request->getPathInfo(), $prefix)) {
                    try {
                        $response = $this->serveEmbbed($event, $conf['app'], $conf['path']);
                    } catch (\sfError404Exception $f) {
                        throw $e;
                    }
                    // must also fire events in sfException::outputStackTrace

                    if ($response->isNotFound()) {
                        throw $e;
                    }

                    if (null !== $this->logger) {
                        $this->logger->info(sprintf('Matched embedded symfony (%s)', $conf['app']));
                    }

                    $event->setResponse($response);

                    return;
                }
            }

            throw $e;
        }
    }

    protected function serveEmbbed(GetResponseEvent $event, $app, $root)
    {
        $container = $this->container;
        $kernel = $container->get('kernel');
        $obLevel = $container->get('request')->headers->get('X-Php-Ob-Level') ?: 0;

        define('SF2_EMBEDDED', true);
        $session_id = $container->get('session')->getId();

        require_once rtrim($root, '/ ').'/config/ProjectConfiguration.class.php';

        $configuration = \ProjectConfiguration::getApplicationConfiguration($app, $kernel->getEnvironment(), $kernel->isDebug());
        $event->getDispatcher()->dispatch('butterweed_sf1_embedder.pre_context');
        $context = \sfContext::createInstance($configuration);
        $eventContext = new ContextEvent($context);
        $event->getDispatcher()->dispatch('butterweed_sf1_embedder.pre_dispatch', $eventContext);
        $context->getController()->dispatch();
        $event->getDispatcher()->dispatch('butterweed_sf1_embedder.post_dispatch', $eventContext);

        // especially needed for response listeners such as debug bar
        $response = $context->getResponse();
        $event = $context->getEventDispatcher()->filter(new \sfEvent($response, 'response.filter_content'), $response->getContent());
        $content = $event->getReturnValue();

        // incase there was a readfile call
        if ((ob_get_level() - $obLevel) && !$content) {
            $stream = new \Symfony\Component\HttpFoundation\StreamedResponse(function () {
                    echo ob_get_flush();
                },
                $response->getStatusCode(), $response->getHttpHeaders()
            );
        }

        return new \Symfony\Component\HttpFoundation\Response(
            $content,
            $response->getStatusCode(), $response->getHttpHeaders()
        );
    }
}