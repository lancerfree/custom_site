<?php

namespace Drupal\custom_site\HttpMiddleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Uses the basic auth information to provide the client credentials for OAuth2.
 */
class SiteRestrictAccess implements HttpKernelInterface {

  /**
   * The wrapped HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Constructs a BasicAuthSwap object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   */
  public function __construct(HttpKernelInterface $http_kernel) {
    $this->httpKernel = $http_kernel;
  }

  /**
   * Handle request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The input request.
   * @param int $type
   *   The type of the request. One of HttpKernelInterface::MASTER_REQUEST or
   *   HttpKernelInterface::SUB_REQUEST.
   * @param bool $catch
   *   Whether to catch exceptions or not.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A Response instance
   * @throws \Exception
   *   When an Exception occurs during processing.
   *
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {

      // Current user not initialized yet
    $logged_in = \Drupal::service('session_configuration')->hasSession($request);

    if ($logged_in) {
      return $this->httpKernel->handle($request, $type, $catch);
    }

    // throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
    $current_path = $request->getPathInfo();
    $normalized = preg_replace('@^[/]+(is|en)[/]*@i', '/', $current_path);
    // Anonymous can access only this
    if (
      ('/user/login' === $normalized)
      || ('/' === $normalized)
    ) {
      $GLOBALS['custom_site_hide_regions'] = TRUE;
      return $this->httpKernel->handle($request, $type, $catch);
    }
    if (
      (strpos($normalized, '/api') === 0)
      || ('/subrequests' === $normalized)
      || ('/router/translate-path' === $normalized)
    ) {
      // return $this->httpKernel->handle($request, $type, $catch);
      return $this->httpKernel->handle($request, $type, $catch);
    }
    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
  }
}