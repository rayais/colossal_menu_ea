<?php

namespace Drupal\Tests\colossal_menu\Functional\ColossalMenuLink;

use Drupal\colossal_menu\Entity\Link;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Colossal Menu Link entity delete UI.
 *
 * @group colossal_menu
 */
class DeleteFormTest extends BrowserTestBase {

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
    $web_user = $this->drupalCreateUser([
      'administer colossal_menu_link',
      'add colossal_menu_link',
      'delete colossal_menu_link',
    ]);
    $this->drupalLogin($web_user);

    $storage = \Drupal::entityTypeManager()->getStorage('colossal_menu');
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $storage->create(['id' => 'tests', 'label' => 'Tests']);
    $entity->save();
  }

  /**
   * Tests the MenuLinkContentDeleteForm class.
   */
  public function testMenuLinkContentDeleteForm() {
    // Create link.
    $this->drupalGet('admin/structure/colossal_menu/tests/link/add');
    $title = 'Front page';
    $this->submitForm([
      'title[0][value]' => $title,
      'link[0][uri]' => '<front>',
    ], 'Save');
    $this->assertSession()->pageTextContains("Created the $title Link.");

    // Delete link.
    $link = Link::load(1);
    $this->drupalGet($link->toUrl('delete-form'));
    $this->assertSession()->pageTextContains("Are you sure you want to delete the link {$link->label()}?");
    $this->assertSession()->linkExists('Cancel');
    $this->assertSession()->linkByHrefExists($link->toUrl('edit-form')->toString());
    $this->submitForm([], 'Delete');
    $this->assertSession()->pageTextContains("The link {$link->label()} has been deleted.");
  }

}
