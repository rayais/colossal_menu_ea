<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\LinkInterface.
 */

namespace Drupal\colossal_menu;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Link entities.
 *
 * @ingroup colossal_menu
 */
interface LinkInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Link type.
   *
   * @return string
   *   The Link type.
   */
  public function getType();

  /**
   * Gets the Menu.
   *
   * @return \Drupal\colossal_menu\MenuInterface
   *   The Menu.
   */
  public function getMenu();

  /**
   * Gets the Link name.
   *
   * @return string
   *   Name of the Link.
   */
  public function getName();

  /**
   * Sets the Link name.
   *
   * @param string $name
   *   The Link name.
   *
   * @return \Drupal\colossal_menu\LinkInterface
   *   The called Link entity.
   */
  public function setName($name);

  /**
   * Gets the Link creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Link.
   */
  public function getCreatedTime();

  /**
   * Sets the Link creation timestamp.
   *
   * @param int $timestamp
   *   The Link creation timestamp.
   *
   * @return \Drupal\colossal_menu\LinkInterface
   *   The called Link entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Link published status indicator.
   *
   * Unpublished Link are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Link is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Link.
   *
   * @param bool $published
   *   TRUE to set this Link to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\colossal_menu\LinkInterface
   *   The called Link entity.
   */
  public function setPublished($published);

}
