<?php

namespace Drupal\Tests\colossal_menu\Functional;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the Link Type entity UI.
 *
 * @group colossal_menu
 */
class LinkTypeFormTest extends ColossalMenuFunctionalTestBase {

  /**
   * Tests adding a Link Type.
   */
  public function testAddLinkType() {
    $this->drupalGet(Url::fromRoute('entity.colossal_menu_link_type.add_form'));
    $this->assertSession()->pageTextContains("Add Link type");
    $this->submitForm(['id' => 'test', 'label' => 'Test'], 'Save');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $link_type = \Drupal::entityTypeManager()->getStorage('colossal_menu_link_type')->load('test');
    $this->assertSession()->pageTextContains("Link type {$link_type->label()} created.");
  }

  /**
   * Tests editing a Link Type.
   */
  public function testEditLinkType() {
    $this->addLinkType();
    $link_type = \Drupal::entityTypeManager()->getStorage('colossal_menu_link_type')->load('test_type');
    $this->drupalGet(Url::fromRoute('entity.colossal_menu_link_type.collection'));
    $this->assertSession()->pageTextContains($link_type->label());
    $this->assertSession()->pageTextContains($link_type->id());
    $this->drupalGet($link_type->toUrl('edit-form'));
    $this->assertSession()->pageTextContains("Edit {$link_type->label()}");
    $this->assertSession()->linkByHrefExists($link_type->toUrl('delete-form')->toString());
    $this->submitForm(['label' => 'Updated Test Type'], 'Save');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->assertSession()->pageTextContains("Link type Updated Test Type updated.");
  }

}
