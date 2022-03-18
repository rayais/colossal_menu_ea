<?php

namespace Drupal\colossal_menu\Form;

use Drupal\colossal_menu\Menu\MenuLinkTree;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\Form\MenuLinkFormInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Link edit forms.
 *
 * @ingroup colossal_menu
 */
class LinkForm extends ContentEntityForm implements MenuLinkFormInterface {

  /**
   * The link tree.
   *
   * @var \Drupal\colossal_menu\Menu\MenuLinkTree
   */
  protected $linkTree;

  /**
   * Constructs a LinkForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\colossal_menu\Menu\MenuLinkTree $link_tree
   *   The colossal menu link tree.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeManagerInterface $entity_type_manager, MenuLinkTree $link_tree, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->entityTypeManager = $entity_type_manager;
    $this->linkTree = $link_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.manager'),
      $container->get('colossal_menu.link_tree'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setMenuLinkInstance(MenuLinkInterface $menu_link) {
    $this->entity = $menu_link;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(array &$form, FormStateInterface $form_state) {
    return $form_state->getUserInput();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $this->buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    return $this->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    return $this->submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $link = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit %label', ['%label' => $link->label()]);
    }

    $form = parent::form($form, $form_state, $link);

    // Override the parent select options to limit them to links of the
    // given menu and display the link hierarchy.
    $form['parent']['widget']['#options'] = $this->parentSelectOptions();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $entity = parent::validateForm($form, $form_state);

    // Check if a parent is present.
    if ($parent = $entity->get('parent')->entity) {
      // Check if the link ID and the parent ID are the same.
      if ($parent->id() == $entity->id()) {
        // Set an error.
        $form_state->setErrorByName('parent', $this->t('The parent cannot be set to the link being edited.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $link = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label Link.', [
          '%label' => $link->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved the %label Link.', [
          '%label' => $link->label(),
        ]));
    }

    $form_state->setRedirect('entity.colossal_menu.edit_form', [
      'colossal_menu' => $link->getMenuName(),
    ]);
  }

  /**
   * Provide the select list options for the parent field.
   *
   * @return array
   *   An array of select list options, keyed by link ID.
   */
  public function parentSelectOptions() {
    // Start the options.
    $options = ['_none' => '- ' . $this->t('None') . ' -'];

    // Load the menu tree for the menu that the link is part of.
    $tree = $this->linkTree->load($this->entity->getMenuName(), new MenuTreeParameters());

    // Recursively add the parent options as a tree.
    $this->parentSelectOptionsRecursive($options, $tree);

    return $options;
  }

  /**
   * Recursive callback to generate the select list options for the link parent.
   *
   * Generates the options as a tree with depth.
   *
   * @param array &$options
   *   The select list options array.
   * @param array $tree
   *   The menu link tree.
   * @param int $depth
   *   The depth of the menu tree.
   */
  protected function parentSelectOptionsRecursive(array &$options, array $tree, int $depth = 0) {
    // Iterate the tree.
    foreach ($tree as $id => $level) {
      // Add the parent link.
      $options[$id] = str_repeat('-', ($depth * 2)) . ' ' . $level->link->label();

      // Recursively add the children.
      $this->parentSelectOptionsRecursive($options, $level->subtree, ($depth + 1));
    }
  }

}
