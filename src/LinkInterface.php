<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\LinkInterface.
 */

namespace Drupal\colossal_menu;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Link entities.
 *
 * @ingroup colossal_menu
 */
interface LinkInterface extends ContentEntityInterface, EntityChangedInterface {

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
   * Gets the Link title.
   *
   * @return string
   *   Title of the Link.
   */
  public function getTitle();

  /**
   * Sets the Link title.
   *
   * @param string $name
   *   The Link title.
   *
   * @return \Drupal\colossal_menu\LinkInterface
   *   The called Link entity.
   */
  public function setTitle($name);

  /**
   * Gets the Link creation datetime.
   *
   * @return \DateTime
   *   Creation dateimte of the Link.
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
  public function isEnabled();

  /**
   * Sets the published status of a Link.
   *
   * @param bool $enabled
   *   TRUE to set this Link to enabled, FALSE to set it to disabled.
   *
   * @return \Drupal\colossal_menu\LinkInterface
   *   The called Link entity.
   */
  public function setEnabled($enabled);

}
