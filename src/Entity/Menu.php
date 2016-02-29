<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\Entity\Menu.
 */

namespace Drupal\colossal_menu\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
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
 *   },
 *   config_prefix = "menu",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/colossal_menu/add",
 *     "edit-form" = "/admin/structure/colossal_menu/{colossal_menu}/edit",
 *     "delete-form" = "/admin/structure/colossal_menu/{colossal_menu}/delete",
 *     "collection" = "/admin/structure/colossal_menu"
 *   }
 * )
 */
class Menu extends ConfigEntityBundleBase implements MenuInterface {
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

}
