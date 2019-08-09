<?php

namespace Drutiny\Http\Audit;

use Drutiny\Sandbox\Sandbox;
use Drutiny\Annotation\Param;
use Drutiny\Annotation\Token;

/**
 *
 * @Param(
 *  name = "header",
 *  description = "The HTTP header to check the value of.",
 *  type = "string"
 * )
 * @Token(
 *  name = "header_value",
 *  description = "The value to check against.",
 *  type = "string"
 * )
 */
class HttpHeaderExists extends Http {

  /**
   *
   */
  public function audit(Sandbox $sandbox)
  {
    $res = $this->getHttpResponse($sandbox);
    if ($has_header = $res->hasHeader($sandbox->getParameter('header'))) {
        $headers = $res->getHeader($sandbox->getParameter('header'));
        $sandbox->setParameter('header_value', $headers[0]);
    }
    return $has_header;
  }
}


 ?>
