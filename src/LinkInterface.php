<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\LinkInterface.
 */

namespace Drupal\colossal_menu;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Menu\MenuLinkInterface;

/**
 * Provides an interface for defining Link entities.
 *
 * @ingroup colossal_menu
 */
interface LinkInterface extends MenuLinkInterface, ContentEntityInterface, EntityChangedInterface {}
