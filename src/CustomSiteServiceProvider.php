<?php

namespace Drupal\custom_site;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class CustomJsonServiceProvider
 *
 * @package Drupal\custom_site
 */
class CustomSiteServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // For extending page_cache
    $definition = $container->getDefinition('http_middleware.page_cache');
    // use  own class
    $definition->setClass('Drupal\custom_site\StackMiddleware\PageCacheExtend');
  }

}