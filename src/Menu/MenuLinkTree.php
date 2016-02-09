<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\Menu\MenuLinkTree.
 */

namespace Drupal\colossal_menu\Menu;

use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTree as CoreMenuLinkTree;
use Drupal\Core\Menu\MenuTreeStorageInterface;
use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Routing\RouteProviderInterface;

/**
 * Implements the loading, transforming and rendering of menu link trees.
 */
class MenuLinkTree extends CoreMenuLinkTree {

  /**
   * Constructs a \Drupal\Core\Menu\MenuLinkTree object.
   *
   * @param \Drupal\Core\Menu\MenuTreeStorageInterface $tree_storage
   *   The menu link tree storage.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider to load routes by name.
   * @param \Drupal\Core\Menu\MenuActiveTrailInterface $menu_active_trail
   *   The active menu trail service.
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controller_resolver
   *   The controller resolver.
   */
  public function __construct(MenuTreeStorageInterface $tree_storage,
                              RouteProviderInterface $route_provider,
                              MenuActiveTrailInterface $menu_active_trail,
                              ControllerResolverInterface $controller_resolver) {
    $this->treeStorage = $tree_storage;
    $this->routeProvider = $route_provider;
    $this->menuActiveTrail = $menu_active_trail;
    $this->controllerResolver = $controller_resolver;
  }


  /**
   * {@inheritdoc}
   */
  protected function createInstances(array $data_tree) {
    $tree = array();
    foreach ($data_tree as $key => $element) {
      $subtree = $this->createInstances($element['subtree']);
      // Build a MenuLinkTreeElement out of the menu tree link definition:
      // transform the tree link definition into a link definition and store
      // tree metadata.
      $tree[$key] = new MenuLinkTreeElement(
        $element['link'],
        (bool) $element['has_children'],
        (int) $element['depth'],
        (bool) $element['in_active_trail'],
        $subtree
      );
    }
    return $tree;
  }

}
