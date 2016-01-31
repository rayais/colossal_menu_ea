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
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\colossal_menu\Entity\Link */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    return $form;
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
    $form_state->setRedirect('entity.colossal_menu_link.edit_form', ['colossal_menu_link' => $entity->id()]);
  }

}
