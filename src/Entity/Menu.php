<?php

namespace Drupal\colossal_menu\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\system\MenuInterface;

/**
 * Defines the Menu entity.
 *
 * @ConfigEntityType(
 *   id = "colossal_menu",
 *   label = @Translation("Colossal Menu"),
 *   handlers = {
 *     "list_builder" = "Drupal\colossal_menu\MenuListBuilder",
 *     "form" = {
 *       "add" = "Drupal\colossal_menu\Form\MenuForm",
 *       "edit" = "Drupal\colossal_menu\Form\MenuForm",
 *       "delete" = "Drupal\colossal_menu\Form\MenuDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\colossal_menu\MenuAccessControlHandler",
 *   },
 *   config_prefix = "menu",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/colossal_menu/add",
 *     "edit-form" = "/admin/structure/colossal_menu/{colossal_menu}/edit",
 *     "delete-form" = "/admin/structure/colossal_menu/{colossal_menu}/delete",
 *     "collection" = "/admin/structure/colossal_menu"
 *   }
 * )
 */
class Menu extends ConfigEntityBase implements MenuInterface {
  /**
   * The Menu ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Menu label.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);
    /** @var \Drupal\colossal_menu\Menu\MenuTreeStorage $menu_tree_storage */
    $menu_tree_storage = \Drupal::service('colossal_menu.tree_storage');
    foreach ($entities as $menu) {
      $links = $menu_tree_storage->loadByProperties(['menu' => $menu->id()]);
      // Delete all links from the menu.
      foreach ($links as $link) {
        $link->delete();
      }
    }
  }

}
