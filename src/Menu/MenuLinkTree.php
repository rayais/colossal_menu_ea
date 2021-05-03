<?php

namespace Drupal\colossal_menu\Menu;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTree as CoreMenuLinkTree;
use Drupal\Core\Menu\MenuTreeStorageInterface;
use Drupal\Core\Routing\RouteProviderInterface;

/**
 * Implements the loading, transforming and rendering of menu link trees.
 */
class MenuLinkTree extends CoreMenuLinkTree {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManager service.
   */
  public function __construct(MenuTreeStorageInterface $tree_storage,
                              RouteProviderInterface $route_provider,
                              MenuActiveTrailInterface $menu_active_trail,
                              ControllerResolverInterface $controller_resolver,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->treeStorage = $tree_storage;
    $this->routeProvider = $route_provider;
    $this->menuActiveTrail = $menu_active_trail;
    $this->controllerResolver = $controller_resolver;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function createInstances(array $data_tree) {
    $tree = [];
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

      if ($tree[$key]->link instanceof AccessibleInterface) {
        $tree[$key]->access = $tree[$key]->link->access('view', NULL, TRUE);
      }
    }
    return $tree;
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $tree) {
    $build = parent::build($tree);

    // Use a custom theme.
    if (isset($build['#theme'])) {
      $build['#theme'] = 'colossal_menu__' . strtr($build['#menu_name'], '-', '_');
    }

    if (!empty($build['#items'])) {
      $this->addItemContent($build['#items']);
    }

    return $build;
  }

  /**
   * Add the Link Content and add a no link variable.
   *
   * @param array $tree
   *   Tree of links.
   */
  protected function addItemContent(array &$tree) {
    foreach ($tree as &$item) {
      /** @var \Drupal\colossal_menu\LinkInterface $link */
      $link = $item['original_link'];

      $item['show_title'] = $link->showTitle();
      $item['identifier'] = $link->id();

      $item['has_link'] = TRUE;
      if (!$link->isExternal() && $link->getRouteName() == '<none>') {
        $item['has_link'] = FALSE;
      }

      $item['content'] = $this->entityTypeManager->getViewBuilder($link->getEntityTypeId())->view($link, 'default');
      if (!empty($item['below'])) {
        $this->addItemContent($item['below']);
      }
    }
  }

}
