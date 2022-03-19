<?php

namespace Drupal\colossal_menu\Plugin\Block;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\system\Plugin\Block\SystemMenuBlock;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a generic Colossal Menu block.
 *
 * @Block(
 *   id = "colossal_menu_block",
 *   admin_label = @Translation("Colossal Menu"),
 *   category = @Translation("Colossal Menus"),
 *   deriver = "Drupal\colossal_menu\Plugin\Derivative\MenuBlock"
 * )
 */
class MenuBlock extends SystemMenuBlock {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new MenuBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Menu\MenuActiveTrailInterface $menu_active_trail
   *   The active menu trail service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MenuLinkTreeInterface $menu_tree, MenuActiveTrailInterface $menu_active_trail, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $menu_tree, $menu_active_trail);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('colossal_menu.link_tree'),
      $container->get('menu.active_trail'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $menu_name = $this->getDerivativeId();
    if ($menu = $this->entityTypeManager->getStorage('colossal_menu')->load($menu_name)) {
      return Cache::mergeTags(parent::getCacheTags(), $menu->getCacheTags());
    }
    return parent::getCacheTags();
  }

}
