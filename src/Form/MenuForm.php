<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\Form\MenuForm.
 */

namespace Drupal\colossal_menu\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MenuForm.
 *
 * @package Drupal\colossal_menu\Form
 */
class MenuForm extends EntityForm {

  /**
   * Entity Manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Menu Tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * Constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager, MenuLinkTreeInterface $menu_link_tree) {
    $this->entityManager = $entity_manager;
    $this->menuLinkTree = $menu_link_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('colossal_menu.link_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $menu = $this->entity;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $menu->label(),
      '#description' => $this->t("Label for the Menu."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $menu->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\colossal_menu\Entity\Menu::load',
      ),
      '#disabled' => !$menu->isNew(),
    );

    // Add menu links administration form for existing menus.
    if (!$menu->isNew()) {
      // Form API supports constructing and validating self-contained sections
      // within forms, but does not allow handling the form section's submission
      // equally separated yet. Therefore, we use a $form_state key to point to
      // the parents of the form section.
      // @see self::submitOverviewForm()
      $form_state->set('links', ['links']);
      $form['links'] = array();
      $form['links'] = $this->buildOverviewForm($form['links'], $form_state);
    }

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $menu = $this->entity;

    if (!$menu->isNew()) {
      $this->submitOverviewForm($form, $form_state);
    }

    $status = $menu->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Menu.', [
          '%label' => $menu->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Menu.', [
          '%label' => $menu->label(),
        ]));
    }
    $form_state->setRedirectUrl($menu->urlInfo('collection'));
  }

  /**
   * Submit handler for the menu overview form.
   *
   * This function takes great care in saving parent items first, then items
   * underneath them. Saving items in the incorrect order can break the tree.
   */
  protected function submitOverviewForm(array $complete_form, FormStateInterface $form_state) {
    $input = $form_state->getUserInput();

    foreach ($input['links'] as $id => $input) {
      $storage = $this->entityManager->getStorage('colossal_menu_link');
      $link = $storage->load($id);

      $link->setParent($input['parent']);
      $link->setWeight($input['weight']);
      $link->save();
    }
  }

  /**
   * Form constructor to edit an entire menu tree at once.
   *
   * Shows for one menu the menu links accessible to the current user and
   * relevant operations.
   *
   * This form constructor can be integrated as a section into another form. It
   * relies on the following keys in $form_state:
   * - menu: A menu entity.
   * - menu_overview_form_parents: An array containing the parent keys to this
   *   form.
   * Forms integrating this section should call menu_overview_form_submit() from
   * their form submit handler.
   */
  protected function buildOverviewForm(array &$form, FormStateInterface $form_state) {
    $menu = $this->entity;

    // Ensure that menu_overview_form_submit() knows the parents of this form
    // section.
    if (!$form_state->has('menu_overview_form_parents')) {
      $form_state->set('menu_overview_form_parents', []);
    }

    $form['links'] = [
      '#type' => 'table',
      '#tree' => TRUE,
      '#header' => [
        $this->t('Title'),
        $this->t('Weight'),
        [
          'data' => $this->t('Operations'),
          'colspan' => 3,
        ],
      ],
      '#tabledrag' => [
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'link-parent',
          'subgroup' => 'link-parent',
          'source' => 'link-id',
          'hidden' => TRUE,
        ],
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'link-weight',
        ],
      ],
    ];

    $storage = $this->entityManager->getStorage('colossal_menu_link');
    $ids = $storage->getQuery()
        ->condition('menu', $menu->id())
        ->execute();

    foreach ($storage->loadMultiple($ids) as $id => $link) {
      $form['links'][$id] = [
        '#weight' => $link->getWeight(),
        '#attributes' => [
          'class' => [
            'draggable',
          ],
        ],
      ];

      $form['links'][$id]['indent'] = [
        [
          '#theme' => 'indentation',
          '#size' => 0,
        ],
        [
          '#plain_text' => $link->label(),
        ],
      ];

      $form['links'][$id]['weight'] = [
        '#type' => 'weight',
        '#delta' => count($ids),
        '#default_value' => $link->getWeight(),
        '#title' => $this->t('Weight for @title', array('@title' => $link->getTitle())),
        '#title_display' => 'invisible',
        '#attributes' => [
          'class' => [
            'link-weight',
          ],
        ],
      ];

      $form['links'][$id]['id'] = [
        '#type' => 'hidden',
        '#value' => $id,
        '#attributes' => [
          'class' => [
            'link-id',
          ],
        ],
      ];

      $form['links'][$id]['parent'] = array(
        '#type' => 'hidden',
        '#default_value' => ($link->getParent()) ? $link->getParent()->id() : 0,
        '#attributes' => [
          'class' => [
            'link-parent',
          ],
        ],
      );

    }

    return $form;
  }

}
