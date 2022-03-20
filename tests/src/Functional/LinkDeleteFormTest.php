<?php

namespace Drupal\Tests\colossal_menu\Functional;

/**
 * Tests the Link entity delete UI.
 *
 * @group colossal_menu
 */
class LinkDeleteFormTest extends ColossalMenuFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->addMenu();
    $this->addLinkType();
  }

  /**
   * Tests the LinkDeleteForm class.
   */
  public function testLinkDeleteForm() {
    // Create link.
    $this->drupalGet('admin/structure/colossal_menu/tests/link/add');
    $title = 'Front page';
    $this->submitForm([
      'title[0][value]' => $title,
      'link[0][uri]' => '<front>',
    ], 'Save');
    $this->assertSession()->pageTextContains("Link $title created.");

    // Delete link.
    $link = \Drupal::entityTypeManager()->getStorage('colossal_menu_link')->load(1);
    $this->drupalGet($link->toUrl('delete-form'));
    $this->assertSession()->pageTextContains("Are you sure you want to delete the link {$link->label()}?");
    $this->assertSession()->linkExists('Cancel');
    $this->assertSession()->linkByHrefExists($link->toUrl('edit-form')->toString());
    $this->submitForm([], 'Delete');
    $this->assertSession()->pageTextContains("The link {$link->label()} has been deleted.");
  }

}
