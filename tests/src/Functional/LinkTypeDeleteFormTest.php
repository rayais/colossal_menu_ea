<?php

namespace Drupal\Tests\colossal_menu\Functional;

/**
 * Tests the Link Type entity delete UI.
 *
 * @group colossal_menu
 */
class LinkTypeDeleteFormTest extends ColossalMenuFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->addLinkType();
  }

  /**
   * Tests the MenuDeleteForm class.
   */
  public function testMenuDeleteForm() {
    $link_type = \Drupal::entityTypeManager()->getStorage('colossal_menu_link_type')->load('test_type');
    $this->drupalGet($link_type->toUrl('delete-form'));
    $this->assertSession()->pageTextContains("Are you sure you want to delete the {$link_type->label()} link type?");
    $this->assertSession()->linkExists('Cancel');
    $this->assertSession()->linkByHrefExists($link_type->toUrl('collection')->toString());
    $this->submitForm([], 'Delete');
    $this->assertSession()->pageTextContains("The link type {$link_type->label()} has been deleted.");
  }

}
