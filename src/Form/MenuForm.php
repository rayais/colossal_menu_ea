<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\Form\MenuForm.
 */

namespace Drupal\colossal_menu\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MenuForm.
 *
 * @package Drupal\colossal_menu\Form
 */
class MenuForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $colossal_menu = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $colossal_menu->label(),
      '#description' => $this->t("Label for the Menu."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $colossal_menu->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\colossal_menu\Entity\Menu::load',
      ),
      '#disabled' => !$colossal_menu->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
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

}
