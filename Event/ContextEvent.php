<?php

namespace Butterweed\SF1EmbedderBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ContextEvent extends Event
{
    protected $context;

    public function __construct(\sfContext $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }
}