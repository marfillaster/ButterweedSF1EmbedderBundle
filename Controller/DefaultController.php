<?php

namespace Butterweed\SF1EmbedderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Butterweed\SF1EmbedderBundle\Embedded;

class DefaultController extends Controller
{
    public function indexAction($prefix, $app, $path)
    {
        $embed = new Embedded($this->container);

        return $embed->serve($prefix, $app, $path);
    }
}
