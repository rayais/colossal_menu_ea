<?php

namespace Drupal\Tests\colossal_menu\Functional;

/**
 * Tests the Menu entity delete UI.
 *
 * @group colossal_menu
 */
class MenuDeleteFormTest extends ColossalMenuFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->addMenu();
  }

  /**
   * Tests the MenuDeleteForm class.
   */
  public function testMenuDeleteForm() {
    $menu = \Drupal::entityTypeManager()->getStorage('colossal_menu')->load('tests');
    $this->drupalGet($menu->toUrl('delete-form'));
    $this->assertSession()->pageTextContains("Are you sure you want to delete the {$menu->label()} menu?");
    $this->assertSession()->linkExists('Cancel');
    $this->assertSession()->linkByHrefExists($menu->toUrl('collection')->toString());
    $this->submitForm([], 'Delete');
    $this->assertSession()->pageTextContains("The menu {$menu->label()} has been deleted.");
  }

}
