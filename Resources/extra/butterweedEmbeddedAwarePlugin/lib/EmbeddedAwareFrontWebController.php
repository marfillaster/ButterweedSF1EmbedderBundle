<?php

class EmbeddedAwareFrontWebController extends sfFrontWebController
{
    public function redirect($url, $delay = 0, $statusCode = 302)
    {
        $url = $this->genUrl($url, true);

        if (sfConfig::get('sf_logging_enabled'))
        {
          $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Redirect to "%s"', $url))));
        }

        // redirect
        $response = $this->context->getResponse();
        $response->clearHttpHeaders();
        $response->setStatusCode($statusCode);
        $response->setHttpHeader('Location', $url);
        $response->setContent(sprintf('<html><head><meta http-equiv="refresh" content="%d;url=%s"/></head></html>', $delay, htmlspecialchars($url, ENT_QUOTES, sfConfig::get('sf_charset'))));
        if (!defined('SF2_EMBEDDED')) {
            $response->send();
        } else {
            // doc says forward and redirect must throw sfStopException this is missing as of 1.2
            throw new sfStopException();
        }
    }

    public function dispatch()
    {
        if (!defined('SF2_EMBEDDED')) {
            return parent::dispatch();
        }

        try {
            // reinitialize filters (needed for unit and functional tests)
            sfFilter::$filterCalled = array();

            // determine our module and action
            $request    = $this->context->getRequest();
            $moduleName = $request->getParameter('module');
            $actionName = $request->getParameter('action');

          if (empty($moduleName) || empty($actionName)) {
              throw new sfError404Exception(sprintf('Empty module and/or action after parsing the URL "%s" (%s/%s).', $request->getPathInfo(), $moduleName, $actionName));
          }

          // make the first request
          $this->forward($moduleName, $actionName);
        } catch (sfStopException $e) {
            // do nothing
        }
    }
}