<?php

namespace Drupal\Tests\colossal_menu\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Base class for testing the functionality of Colossal Menu.
 *
 * @group colossal_menu
 */
abstract class ColossalMenuFunctionalTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['colossal_menu'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $admin = $this->drupalCreateUser([
      // Menu permissions.
      'colossal_menu overview',
      'add colossal_menu',
      'delete colossal_menu',
      'edit colossal_menu',
      'view colossal_menu',
      // Link Type permissions.
      'administer colossal_menu_link_type',
      'add colossal_menu_link_type',
      'delete colossal_menu_link_type',
      'edit colossal_menu_link_type',
      'view colossal_menu_link_type',
      // Link permissions.
      'administer colossal_menu_link',
      'add colossal_menu_link',
      'delete colossal_menu_link',
      'edit colossal_menu_link',
      'view enabled colossal_menu_link',
      'view disabled colossal_menu_link',
    ]);
    $this->drupalLogin($admin);
  }

  /**
   * Add a menu entity.
   *
   * @param array $values
   *   Entity values for the menu.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function addMenu(array $values = []): void {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = \Drupal::entityTypeManager()
      ->getStorage('colossal_menu')
      ->create(['id' => 'tests', 'label' => 'Tests'] + $values);
    $entity->save();
  }

  /**
   * Add a link type entity.
   *
   * @param array $values
   *   Entity values for the link type.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function addLinkType(array $values = []): void {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = \Drupal::entityTypeManager()
      ->getStorage('colossal_menu_link_type')
      ->create(['id' => 'test_type', 'label' => 'Test Type'] + $values);
    $entity->save();
  }

}
