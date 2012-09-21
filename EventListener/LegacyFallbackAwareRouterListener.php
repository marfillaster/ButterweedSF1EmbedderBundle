<?php 

namespace Butterweed\SF1EmbedderBundle\EventListener;

use Symfony\Component\HttpKernel\EventListener\RouterListener as BaseRouterListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
                        $response = $this->serveEmbbed($conf['app'], $conf['path']);
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

    protected function serveEmbbed($app, $root)
    {
        $container = $this->container;
        $kernel = $container->get('kernel');
        $obLevel = $container->get('request')->headers->get('X-Php-Ob-Level') ?: 0;

        $session = $container->get('session');
        if (!$session->isStarted()) {
            $session->start();
        }

        define('SF2_EMBEDDED', true);
        $session_id = $container->get('session')->getId();

        require_once rtrim($root, '/ ').'/config/ProjectConfiguration.class.php';

        $configuration = \ProjectConfiguration::getApplicationConfiguration($app, $kernel->getEnvironment(), $kernel->isDebug());
        $context = \sfContext::createInstance($configuration);
        $context->getController()->dispatch();

        // this is a bit flaky, may only be compatible with sfSessionStorage
        // persist user attr to session
        $context->getUser()->shutdown();

        // not needed when authentication is provided by sf2 which must also update tokens in sfGuardUserPlugin session namespace
        if ($session->isStarted() && ($session_id != session_id())) {
            $session->migrate();
        }

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