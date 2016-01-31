<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\LinkListBuilder.
 */

namespace Drupal\colossal_menu;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Link entities.
 *
 * @ingroup colossal_menu
 */
class LinkListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Link ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\colossal_menu\Entity\Link */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.colossal_menu_link.edit_form', array(
          'colossal_menu_link' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
