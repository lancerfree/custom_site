<?php

namespace Drupal\custom_site\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Api pages is needed cache for authenticated users too.
 *
 * @internal
 */
class APIAuthenticateAllowRequestPolicy implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function isAPIPath(Request $request) {
    $current_path = $request->getPathInfo();
    $normalized = preg_replace('@^[/]+(is|en)[/]*@i', '/', $current_path);
    if (
      (strpos($normalized, '/api') === 0)
      || ('/subrequests' === $normalized)
      || ('/router/translate-path' === $normalized)
    ) {
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    return $this->isAPIPath($request) ? static::ALLOW : NULL;
  }

}
