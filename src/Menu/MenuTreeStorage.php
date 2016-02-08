<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\Menu\MenuTreeStorage.
 */

namespace Drupal\colossal_menu\Menu;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Menu\MenuTreeStorageInterface;
use Drupal\Core\Url;

/**
 * Provides a menu tree storage using the database.
 */
class MenuTreeStorage implements MenuTreeStorageInterface {

  /**
   * The maximum depth of a menu links tree.
   *
   * This storage has no theoretical limit, but we'll set a reasonable limit.
   */
  const MAX_DEPTH = 20;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The database table name.
   *
   * @var string
   */
  protected $table;

  /**
   * Constructs a new \Drupal\Core\Menu\MenuTreeStorage.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A Database connection to use for reading and writing configuration data.
   * @param string $table
   *   A database table name to store configuration data in.
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entity_type_manager, $entity_type, $table) {
    $this->connection = $connection;
    $this->storage = $entity_type_manager->getStorage($entity_type);
    $this->table = $table;
  }

  /**
   * {@inheritdoc}
   */
  public function maxDepth() {
    return self::MAX_DEPTH;
  }

  /**
   * {@inheritdoc}
   *
   * Allow the entity system to cache the results.
   */
  public function resetDefinitions() {}

  /**
   * {@inheritdoc}
   *
   * Allow the entity system to cache the results.
   */
  public function rebuild(array $definitions) {}

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    return $this->storage->load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids) {
    return $this->storage->loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function loadByProperties(array $properties) {
    return $this->storage->loadByProperties($properties);
  }

  /**
   * {@inheritdoc}
   */
  public function loadByRoute($route_name, array $route_parameters = array(), $menu_name = NULL) {
    $url = new Url($route_name, $route_parameters);

    $query = $this->storage->getQuery();
    $query->condition('link__uri', $url->getUri());

    if ($menu_name) {
      $query->condition('menu', $menu_name);
    }

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $definition) {
    return $this->storage->create($definition);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($id) {
    return $this->storage->delete($this->storage->load($id));
  }

  /**
   * {@inheritdoc}
   */
  public function loadTreeData($menu_name, MenuTreeParameters $parameters) {
    $query = $this->connection->select($this->table, 't')
      ->fields('t', ['ancestor', 'descendant'])
      ->innerJoin($this->storage->getEntityType()->get('base_table'), 'e', 't.ancestor = e.id')
      ->condition('e.menu', $menu_name)
      ->orderBy('t.depth', 'ASC')
      ->orderBy('t.weight', 'ASC');

    if ($parameters->root) {
      $query->condition('t.ancestor', $parameters->root);
    }

    if ($parameters->minDepth) {
      // Since the default depth is 1, and in our storage it's 0, we'll
      // decrement the minimum depth.
      $query->condition('t.depth', '>=', $parameters->minDepth - 1);
    }

    if ($parameters->maxDepth) {
      $query->condition('t.depth', '<=', $parameters->maxDepth);
    }

    $result = $query->execute();

    $tree = [];
    while ($row = $result->fetchObject()) {
      $tree[$row->ancestor][] = $row->descendant;
    }

    $links = $this->loadMultiple(array_keys($tree));

    $routes = [];
    foreach ($links as $link) {
      $url = Url::fromUri($link->get('link')->uri);
      if (!$url->isExternal()) {
        $routes[$link->id()] = $url->getRouteName();
      }
    }

    $this->treeDataRecursive($tree);

    return [$tree, $routes];
  }

  /**
   * Build the tree from the closure table.
   *
   * @param array $tree
   *   A fully built tree that will be modified.
   * @param array $ancestors
   *   An array of ancestors.
   */
  protected function treeDataRecursive(array &$tree, array $ancestors = []) {
    if (count($ancestors) == 1) {
      $tree[array_pop($ancestors)] = [];
      return;
    }
    $next = array_intersect($ancestors, array_keys($tree));
    $this->treeDataRecursive($tree[array_pop($next)], array_diff($ancestors, $next));
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllChildren($id, $max_relative_depth = NULL) {
    $query = $this->connection->select($this->table, 't');
    $query->fields('t', ['descendant']);
    $query->condition('t.ancestor', $id);

    if ($max_relative_depth) {
      $query->condition('t.depth', '<=', $max_relative_depth);
    }

    $query->orderBy('t.depth', 'ASC');

    $ids = $query->execute()->fetchCol();

    return $this->storage->getQuery()
      ->condition('id', $ids)
      ->orderBy('weight', 'ASC');
  }

  /**
   * {@inheritdoc}
   */
  public function getAllChildIds($id) {
    return $this->connection->select($this->table, 't')
      ->fields('t', ['descendant'])
      ->condition('t.ancestor', $id)
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function loadSubtreeData($id, $max_relative_depth = NULL) {
    $link = $this->load($id);
    $params = new MenuTreeParameters();
    $params->root = $id;
    $params->setMaxDepth($max_relative_depth);
    return $this->loadTreeData($link->getMenuName(), $params);
  }

  /**
   * {@inheritdoc}
   */
  public function getRootPathIds($id) {
    return $this->conneciton->select($this->table, 't')
      ->field('t', ['ancestor'])
      ->condition('t.descendant', $id)
      ->orderBy('t.depth', 'DESC')
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function getExpanded($menu_name, array $parents) {
    return $this->conneciton->select($this->table, 't')
      ->field('t', ['descendant'])
      ->innerJoin($this->storage->getEntityType()->get('base_table'), 'e', 't.ancestor = e.id')
      ->condition('t.ancestor', $parents)
      ->condition('e.menu', $menu_name)
      ->orderBy('t.depth', 'ASC')
      ->orderBy('e.weight', 'ASC')
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function getSubtreeHeight($id) {
    return $this->conneciton->select($this->table, 't')
      ->field('t', ['depth'])
      ->condition('t.descendant', $id)
      ->orderBy('t.depth', 'DESC')
      ->limit(0, 1)
      ->execute()
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function menuNameInUse($menu_name) {
    $links = $this->storage->loadByProperties([
      'menu' => $menu_name,
    ]);

    return empty($links);
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuNames() {
    return $this->connection->select($this->storage->getEntityType()->get('base_table'), 'e')
      ->distinct()
      ->field('e', ['menu'])
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countMenuLinks($menu_name = NULL) {
    $query = $this->connection->select($this->storage->getEntityType()->get('base_table'), 'e')
      ->count();

    if ($menu_name) {
      $query->condition('e.menu', $menu_name);
    }

    return $query->execute();
  }

}
