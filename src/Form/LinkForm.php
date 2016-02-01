<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\Form\LinkForm.
 */

namespace Drupal\colossal_menu\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Link edit forms.
 *
 * @ingroup colossal_menu
 */
class LinkForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $link = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit %label', array('%label' => $link->label()));
    }

    return parent::form($form, $form_state, $link);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Link.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Link.', [
          '%label' => $entity->label(),
        ]));
    }

    $form_state->setRedirect('entity.colossal_menu_link.edit_form', [
      'colossal_menu' => $entity->getMenu()->id(),
      'colossal_menu_link' => $entity->id(),
    ]);
  }

}
