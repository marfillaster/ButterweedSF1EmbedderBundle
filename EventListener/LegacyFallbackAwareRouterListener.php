<?php 

namespace Butterweed\SF1EmbedderBundle\EventListener;

use Symfony\Component\HttpKernel\EventListener\RouterListener as BaseRouterListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Butterweed\SF1EmbedderBundle\Embedded;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Routing\RequestContext;

class LegacyFallbackAwareRouterListener extends BaseRouterListener implements ContainerAwareInterface
{
    protected $container, $embbeds;
    private $logger;

    public function __construct($matcher, RequestContext $context = null, LoggerInterface $logger = null)
    {
        parent::__construct($matcher, $context, $logger);
        $this->logger = $logger;
    }

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
                    if (null !== $this->logger) {
                        $this->logger->info(sprintf('Matched embedded symfony (%s)', $conf['app']));
                    }

                    try {
                        $embedded = new Embedded($this->container);
                        $obLevel = $this->container->get('request')->headers->get('X-Php-Ob-Level') ?: 0;
                        $sfResponse = $embedded->serve($conf['app'], $conf['path']);

                        if ((ob_get_level() > $obLevel) && !$sfResponse->getContent()) {
                            $sfResponse = new \Symfony\Component\HttpFoundation\StreamedResponse(function () {
                                    echo ob_get_flush();
                                },
                                $response->getStatusCode(), $sfResponse->getHttpHeaders()
                            );
                        } else {
                            $response = new \Symfony\Component\HttpFoundation\Response(
                                $sfResponse->getContent(),
                                $sfResponse->getStatusCode(), $sfResponse->getHttpHeaders()
                            );
                        }

                        $event->setResponse($response);
                    } catch (\sfError404Exception $f) {
                        $e->setMessage($f->getMessage());

                        throw $e;
                    }
                    // unhandled sfExceptions will be caught by Symfony2 exception handler

                    return;
                }
            }

            throw $e;
        }
    }
}