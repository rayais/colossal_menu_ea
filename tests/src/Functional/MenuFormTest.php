<?php

namespace Drupal\Tests\colossal_menu\Functional;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the Menu entity UI.
 *
 * @group colossal_menu
 */
class MenuFormTest extends ColossalMenuFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Tests adding a Menu.
   */
  public function testAddMenu() {
    $this->drupalGet(Url::fromRoute('entity.colossal_menu.add_form'));
    $this->assertSession()->pageTextContains('Add Colossal Menu');
    $this->submitForm(['id' => 'test_menu', 'label' => 'Test Menu'], 'Save');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $link_type = \Drupal::entityTypeManager()->getStorage('colossal_menu')->load('test_menu');
    $this->assertSession()->pageTextContains("Menu {$link_type->label()} created.");
  }

  /**
   * Tests editing a Menu.
   */
  public function testEditMenu() {
    $this->addMenu();
    $menu = \Drupal::entityTypeManager()->getStorage('colossal_menu')->load('tests');
    $this->drupalGet(Url::fromRoute('entity.colossal_menu.collection'));
    $this->assertSession()->pageTextContains($menu->label());
    $this->assertSession()->pageTextContains($menu->id());
    $this->drupalGet($menu->toUrl('edit-form'));
    $this->assertSession()->pageTextContains("Edit {$menu->label()}");
    $this->assertSession()->linkByHrefExists($menu->toUrl('delete-form')->toString());
    $this->submitForm(['label' => 'Updated Test Menu'], 'Save');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->assertSession()->pageTextContains("Menu Updated Test Menu updated.");
  }

}
