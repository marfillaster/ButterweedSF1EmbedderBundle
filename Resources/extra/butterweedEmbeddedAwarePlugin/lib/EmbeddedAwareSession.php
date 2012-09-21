<?php


class EmbeddedAwareSession extends sfSessionStorage
{
    public function initialize($options = null)
    {
        if (defined('SF2_EMBEDDED')) {
            $options['session_id'] = session_id();
            $options['session_name'] = session_name();
            self::$sessionStarted = true;
        }

        parent::initialize($options);
    }
}