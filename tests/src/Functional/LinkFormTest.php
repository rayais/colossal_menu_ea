<?php

namespace Drupal\Tests\colossal_menu\Functional;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the Link entity UI.
 *
 * @group colossal_menu
 */
class LinkFormTest extends ColossalMenuFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->addMenu();
    $this->addLinkType();
  }

  /**
   * Tests adding a Link.
   */
  public function testAddLink() {
    $link_type = \Drupal::entityTypeManager()->getStorage('colossal_menu_link_type')->load('test_type');
    $this->drupalGet(Url::fromRoute('entity.colossal_menu_link.add_form', [
      'colossal_menu' => 'tests',
      'colossal_menu_link_type' => $link_type->id(),
    ]));
    $this->assertSession()->pageTextContains("Add {$link_type->label()} link");
    $this->submitForm([
      'title[0][value]' => 'Front page',
      'link[0][uri]' => '<front>',
    ], 'Save');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $link = \Drupal::entityTypeManager()->getStorage('colossal_menu_link')->load(1);
    $this->assertSession()->pageTextContains("Link {$link->label()} created.");
  }

  /**
   * Tests editing a Link.
   */
  public function testEditLink() {
    // Create link.
    $link_type = \Drupal::entityTypeManager()->getStorage('colossal_menu_link_type')->load('test_type');
    $this->drupalGet(Url::fromRoute('entity.colossal_menu_link.add_form', [
      'colossal_menu' => 'tests',
      'colossal_menu_link_type' => $link_type->id(),
    ]));
    $this->submitForm([
      'title[0][value]' => 'Front page',
      'link[0][uri]' => '/node',
    ], 'Save');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Edit link.
    $link = \Drupal::entityTypeManager()->getStorage('colossal_menu_link')->load(1);
    $this->drupalGet($link->toUrl('edit-form'));
    $this->assertSession()->pageTextContains("Edit {$link->label()}");
    $this->assertSession()->linkByHrefExists($link->toUrl('delete-form')->toString());
    $this->submitForm([
      'title[0][value]' => 'Updated front page',
      'link[0][uri]' => '<front>',
    ], 'Save');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->assertSession()->pageTextContains("Link Updated front page updated.");
    \Drupal::entityTypeManager()->getStorage('colossal_menu_link')->resetCache();
    $link = \Drupal::entityTypeManager()->getStorage('colossal_menu_link')->load($link->id());
    $this->assertEquals('internal:/', $link->link->uri);
  }

}
