<?php


class EmbedAwareWebRequest extends sfWebRequest
{
    public function getPathInfo()
    {
        if (defined('SF2_EMBEDDED_PATHINFO')) {
            return SF2_EMBEDDED_PATHINFO;
        }

        return parent::getPathInfo();
    }

    public function getPathInfoPrefix()
    {
        if (defined('SF2_EMBEDDED_PATHINFO_PREFIX')) {
            return SF2_EMBEDDED_PATHINFO_PREFIX;
        }

        return parent::getPathInfoPrefix();
    }

    public function getRelativeUrlRoot()
    {
        if (defined('SF2_EMBEDDED_RELATIVE_URL_ROOT')) {
            return SF2_EMBEDDED_RELATIVE_URL_ROOT;
        }

        return parent::getRelativeUrlRoot();
    }
}