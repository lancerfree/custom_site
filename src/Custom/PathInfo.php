<?php

namespace Drupal\custom_site\Custom;

use Symfony\Component\HttpFoundation\Request;


/**
 * Custom Class with path info.
 */
class PathInfo {

  /**
   * The current request HTTP kernel.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  private $request;

  /**
   * The current uri.
   *
   * @var string
   */
  private $uri;

  /**
   * The current lang.
   *
   * @var string
   */
  private $lang;

  /**
   * Path with lang prefix.
   *
   * @var string
   */
  private $prefixedPath;

  /**
   * Constructor
   *
   * @param Request $request
   */
  function __construct(Request $request = NULL) {
    $this->request = $request ?? $GLOBALS["request"];
    $current_path = $this->request->getPathInfo();
    $this->prefixedPath = $current_path;
    $this->lang = (substr($current_path, 0, 3) === '/en') ? 'en' : 'is';
    $this->uri = preg_replace('@^[/]+(is|en)[/]*@i', '/', $current_path);
  }

  /**
   * Is same uri test.
   *
   * @param string $test_uri
   *   String for testing.
   * @return bool
   */
  function isURI($test_uri) {
    return $this->uri === $test_uri;
  }

  /**
   * URI start with test.
   *
   * @param string $test_uri
   *   Test string.
   * @return bool
   */
  function startURI($test_uri) {
    return substr($this->uri, 0, strlen($test_uri)) === $test_uri;
  }

  /**
   * Replaces string and return one at once.
   *
   * @param string $start_path
   *   Start string.
   * @param string $replace_path
   *   Replace string.
   * @param bool $inall
   *   Match all flag.
   * @return mixed|string
   */
  public function replaceURI($start_path, $replace_path, $inall = false) {
    $test_path_len = strlen($start_path);
    if ($inall) {
      $str_replaced = str_replace($start_path, $replace_path, $this->uri);
      if ($str_replaced !== $this->uri) {
        return $str_replaced;
      }
    }
    if (substr($this->uri, 0, $test_path_len) === $start_path) {
      return $replace_path . substr($this->uri, $test_path_len);
    }
  }

  /**
   * Return current uri.
   *
   * @return string
   */
  function getURI() {
    return $this->uri;
  }

  /**
   * Returns prefixed path.
   *
   * @return string
   */
  function getPrefixedPath() {
    return $this->prefixedPath;
  }
  /**
   * Return current language.
   *
   * @return string
   */
  function getLang() {
    return $this->lang;
  }

  /**
   * Return current request.
   *
   * @return Request
   */
  function getRequest() {
    return $this->request;
  }

}