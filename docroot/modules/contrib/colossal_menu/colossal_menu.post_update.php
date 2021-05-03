<?php

/**
 * @file
 * Post update functions for Colossal Menu.
 */

use Drupal\Component\Utility\Random;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Remove deprecated link `machine_name` field.
 */
function colossal_menu_post_update_remove_link_machine_name(&$sandbox = NULL) {
  /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository */
  $entity_display_repository = \Drupal::service('entity_display.repository');
  $link_type_storage = Drupal::entityTypeManager()
    ->getStorage('colossal_menu_link_type');

  // A random string is appended to the new field name to avoid conflicts.
  $random = new Random();
  $field_name = 'field_machine_name_' . strtolower($random->name());

  // Create storage for a field to copy existing machine names in to.
  FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'colossal_menu_link',
    'type' => 'string',
    'cardinality' => 1,
  ])->save();

  // Add new field instance to all link types.
  /** @var \Drupal\colossal_menu\LinkTypeInterface $link_type */
  foreach ($link_type_storage->loadMultiple() as $link_type) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'colossal_menu_link',
      'bundle' => $link_type->id(),
      'label' => t('Machine name (deprecated)'),
      'description' => t('This field is no longer used by the Colossal Menu module and can be removed.'),
    ])->save();
    $entity_display_repository
      ->getFormDisplay('colossal_menu_link', $link_type->id())
      ->setComponent($field_name, ['type' => 'string_textfield'])
      ->save();
  }

  // Copy data from deprecated base field to new field for all links.
  // A direct database query is used here because the Link entity no longer
  // defines the `machine_name` base field.
  $machine_names = Drupal::database()->select('colossal_menu_link', 'cml')
    ->fields('cml', ['id', 'machine_name'])
    ->execute()
    ->fetchAllKeyed();
  $link_storage = Drupal::entityTypeManager()
    ->getStorage('colossal_menu_link');
  /** @var \Drupal\colossal_menu\Entity\Link $link */
  foreach ($link_storage->loadMultiple() as $link) {
    $link->set($field_name, $machine_names[$link->id()]);
    $link->save();
  }

  // Uninstall base field.
  /** @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface $update_manager */
  $update_manager = Drupal::service('entity.definition_update_manager');
  $definition = $update_manager->getFieldStorageDefinition('machine_name', 'colossal_menu_link');
  $update_manager->uninstallFieldStorageDefinition($definition);
}
