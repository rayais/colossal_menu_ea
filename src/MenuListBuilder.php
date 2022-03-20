<?php

namespace Drupal\colossal_menu;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Menu entities.
 */
class MenuListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Menu');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    // Since links are managed from the menu edit page we remove the destination
    // parameter to make it easier to modify and save link changes quickly.
    /** @var \Drupal\Core\Url $url */
    $url = $operations['edit']['url'];
    $query = $url->getOption('query');
    unset($query['destination']);
    $url->setOption('query', $query);

    return $operations;
  }

}
