<?php

namespace Butterweed\SF1EmbedderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Butterweed\SF1EmbedderBundle\Embedded;

class DefaultController extends Controller
{
    public function indexAction($prefix, $app, $path)
    {
        try {
            $embedded = new Embedded($this->container);
            $obLevel = $this->container->get('request')->headers->get('X-Php-Ob-Level') ?: 0;
            $sfResponse = $embedded->serve($prefix, $app, $path);

            if ((ob_get_level() > $obLevel) && !$sfResponse->getContent()) {
                $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () {
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

            return $response;
        } catch (\sfError404Exception $f) {
            throw $this->createNotFoundException($f->getMessage());
        }
    }
}
