<?php

namespace Drupal\colossal_menu;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Link Type entity.
 *
 * @see \Drupal\colossal_menu\Entity\LinkType.
 */
class LinkTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\colossal_menu\LinkInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIf($account->hasPermission('view colossal_menu_link_type'));

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit colossal_menu_link_type');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete colossal_menu_link_type');
    }

    // @todo Fall back on a less permissive access result.
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add colossal_menu_link_type');
  }

}
