<?php

class EmbeddedAwareRenderingFilter extends sfRenderingFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain The filter chain.
   *
   * @throws <b>sfInitializeException</b> If an error occurs during view initialization
   * @throws <b>sfViewException</b>       If an error occurs while executing the view
   */
  public function execute($filterChain)
  {
    // execute next filter
    $filterChain->execute();

    // get response object
    $response = $this->context->getResponse();

    // hack to rethrow sfForm and|or sfFormField __toString() exceptions (see sfForm and sfFormField)
    if (sfForm::hasToStringException())
    {
      throw sfForm::getToStringException();
    }
    else if (sfFormField::hasToStringException())
    {
      throw sfFormField::getToStringException();
    }

    // send headers + content
    if (!defined('SF2_EMBEDDED')) {
      $response->send();
    }

  }
}