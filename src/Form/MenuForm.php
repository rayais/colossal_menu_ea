<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\Form\MenuForm.
 */

namespace Drupal\colossal_menu\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
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
   * Constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
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
    if (!$menu->isNew() || $menu->isLocked()) {
      // Form API supports constructing and validating self-contained sections
      // within forms, but does not allow handling the form section's submission
      // equally separated yet. Therefore, we use a $form_state key to point to
      // the parents of the form section.
      // @see self::submitOverviewForm()
      $form_state->set('menu_overview_form_parents', ['links']);
      $form['links'] = array();
      $form['links'] = $this->buildOverviewForm($form['links'], $form_state);
    }

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $colossal_menu = $this->entity;
    $status = $colossal_menu->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Menu.', [
          '%label' => $colossal_menu->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Menu.', [
          '%label' => $colossal_menu->label(),
        ]));
    }
    $form_state->setRedirectUrl($colossal_menu->urlInfo('collection'));
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

    $form['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Label'),
      ],
    ];

    $storage = $this->entityManager->getStorage('colossal_menu_link');
    $ids = $storage->getQuery()
        ->condition('menu', $menu->id())
        ->execute();

    foreach ($storage->loadMultiple($ids) as $id => $link) {
      $form['table'][$id]['label'] = [
        '#plain_text' => $link->label(),
      ];
    }

    return $form;
  }

}
