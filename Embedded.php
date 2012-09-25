<?php

namespace Butterweed\SF1EmbedderBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Butterweed\SF1EmbedderBundle\Event\ContextEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Cookie;
class Embedded
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Response
     * @throws \sfError404Exception
     * @throws \sfException
     */
    public function serve($prefix, $app, $root)
    {
        $prefix = rtrim($prefix, '/ ');
        $root = rtrim($root, '/ ');
        $container = $this->container;
        $kernel = $container->get('kernel');
        $request = $container->get('request');
        $obLevel = $container->get('request')->headers->get('X-Php-Ob-Level') ?: 0;
        $dispatcher = $container->get('event_dispatcher');

        define('SF2_EMBEDDED', true);
        define('SF2_EMBEDDED_PATHINFO_PREFIX', $request->getBaseUrl().$prefix);
        define('SF2_EMBEDDED_RELATIVE_URL_ROOT', $prefix);
        define('SF2_EMBEDDED_PATHINFO', preg_replace('#^'.preg_quote($prefix).'#', '', $request->getPathInfo()));

        $session_id = $container->get('session')->getId();

        require_once $root.'/config/ProjectConfiguration.class.php';

        $configuration = \ProjectConfiguration::getApplicationConfiguration($app, $kernel->getEnvironment(), $kernel->isDebug());
        $dispatcher->dispatch('butterweed_sf1_embedder.pre_context');
        $context = \sfContext::createInstance($configuration);
        $eventContext = new ContextEvent($context);
        $dispatcher->dispatch('butterweed_sf1_embedder.pre_dispatch', $eventContext);

        try {
            $context->getController()->dispatch();
        } catch (\sfError404Exception $f) {
                throw new NotFoundHttpException($f->getMessage());
        }

        $dispatcher->dispatch('butterweed_sf1_embedder.post_dispatch', $eventContext);

        // especially needed for response listeners such as debug bar
        $response = $context->getResponse();
        $event = $context->getEventDispatcher()->filter(new \sfEvent($response, 'response.filter_content'), $response->getContent());
        $response->setContent($event->getReturnValue());

        if ((ob_get_level() > $obLevel) && !$response->getContent()) {
            $resp = new \Symfony\Component\HttpFoundation\StreamedResponse(function () {
                    echo ob_get_flush();
                },
                $response->getStatusCode(), $response->getHttpHeaders()
            );
        } else {
            $resp = new \Symfony\Component\HttpFoundation\Response(
                $response->getContent(),
                $response->getStatusCode(), $response->getHttpHeaders()
            );
        }

        foreach ($response->getCookies() as $cookie) {
            $c = new Cookie(
                $cookie['name'],
                $cookie['value'],
                in_numeric($cookie['expire']) ? (int) $cookie['expire'] : strtotime($cookie['expire']),
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httpOnly']);

            $resp->headers->setCookie($c);
        }

        return $resp;
    }


}