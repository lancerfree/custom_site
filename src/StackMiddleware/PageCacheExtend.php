<?php

namespace Drupal\custom_site\StackMiddleware;

use Drupal\Core\Cache\CacheableResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\page_cache\StackMiddleware\PageCache;


/**
 * Altered/Extended PageCache class
 * Executes the page caching before the main kernel takes over the request.
 */
class PageCacheExtend extends PageCache {

  /**
   * Overwritten method!
   *
   * Fetches a response from the backend and stores it in the cache.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request object.
   * @param int $type
   *   The type of the request (one of HttpKernelInterface::MASTER_REQUEST or
   *   HttpKernelInterface::SUB_REQUEST)
   * @param bool $catch
   *   Whether to catch exceptions or not
   *
   * @returns \Symfony\Component\HttpFoundation\Response $response
   *   A response object.
   * @see drupal_page_header()
   *
   */
  protected function fetch(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    /** @var \Symfony\Component\HttpFoundation\Response $response */
    $response = $this->httpKernel->handle($request, $type, $catch);
/*    if ($response instanceof CacheableResponseInterface) {
      $this->addCacheParams($response);
    }*/
    // Only set the 'X-Drupal-Cache' header if caching is allowed for this
    // response.
    if ($this->storeResponse($request, $response)) {
      $response->headers->set('X-Drupal-Cache', 'MISS');
    }

    return $response;
  }

  /**
   * Overwritten method!
   *
   * Returns a response object from the page cache.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request object.
   * @param bool $allow_invalid
   *   (optional) If TRUE, a cache item may be returned even if it is expired or
   *   has been invalidated. Such items may sometimes be preferred, if the
   *   alternative is recalculating the value stored in the cache, especially
   *   if another concurrent request is already recalculating the same value.
   *   The "valid" property of the returned object indicates whether the item is
   *   valid or not. Defaults to FALSE.
   *
   * @return \Symfony\Component\HttpFoundation\Response|false
   *   The cached response or FALSE on failure.
   */
  protected function get(Request $request, $allow_invalid = FALSE) {
    $cid = $this->getCacheId($request);
    // Fix for jsonapi module - is not added _format
    if ($this->isJSONApiPath($request) && substr($cid, -1) === ':') {
      $cid .= 'api_json';
    }
    if ($cache = $this->cache->get($cid, $allow_invalid)) {
      return $cache->data;
    }
    return FALSE;
  }

  /**
   * Adds cache time for response.
   *
   * @param $response
   * @throws \Exception
   */
  protected function addCacheParams($response) {
    $date = new \DateTime();
    $date->modify('+600 seconds');
    $response->setExpires($date);
  }

  /**
   * Test path.
   *
   * Fetches a response from the backend and stores it in the cache.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return TRUE | NULL
   */
  public function isJSONApiPath($request) {
    $current_path = $request->getPathInfo();
    $normalized = preg_replace('@^[/]+(is|en)[/]*@i', '/', $current_path);
    if (
    (strpos($normalized, '/api') === 0)
    ) {
      return TRUE;
    }
  }
}
