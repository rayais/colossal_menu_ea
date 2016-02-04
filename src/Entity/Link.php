<?php

/**
 * @file
 * Contains \Drupal\colossal_menu\Entity\Link.
 */

namespace Drupal\colossal_menu\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\colossal_menu\LinkInterface;
use Drupal\link\LinkItemInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Link entity.
 *
 * @ingroup colossal_menu
 *
 * @ContentEntityType(
 *   id = "colossal_menu_link",
 *   label = @Translation("Link"),
 *   bundle_label = @Translation("Link type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\colossal_menu\LinkListBuilder",
 *     "views_data" = "Drupal\colossal_menu\Entity\LinkViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\colossal_menu\Form\LinkForm",
 *       "add" = "Drupal\colossal_menu\Form\LinkForm",
 *       "edit" = "Drupal\colossal_menu\Form\LinkForm",
 *       "delete" = "Drupal\colossal_menu\Form\LinkDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\colossal_menu\LinkAccessControlHandler",
 *   },
 *   base_table = "colossal_menu_link",
 *   admin_permission = "administer link entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/colossal_menu/{colossal_menu}/link/{colossal_menu_link}",
 *     "add-form" = "/admin/structure/colossal_menu/{colossal_menu}/link/add",
 *     "edit-form" = "/admin/structure/colossal_menu/{colossal_menu}/link/{colossal_menu_link}",
 *     "delete-form" = "/admin/structure/colossal_menu/{colossal_menu}/link/{colossal_menu_link}/delete",
 *   },
 *   bundle_entity_type = "colossal_menu_link_type",
 *   field_ui_base_route = "entity.colossal_menu_link_type.edit_form"
 * )
 */
class Link extends ContentEntityBase implements LinkInterface {
  use EntityChangedTrait;
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return $this->get('menu')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setChangedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (bool) $this->getEntityKey('enabled');
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($enabled) {
    $this->set('status', $enabled ? 1 : 0);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $params = parent::urlRouteParameters($rel);

    if (in_array($rel, ['canonical', 'edit-form', 'delete-form'])) {
      $params['colossal_menu'] = $this->getMenu()->id();
    }

    return $params;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Link entity.'))
      ->setReadOnly(TRUE);

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The Link type/bundle.'))
      ->setSetting('target_type', 'colossal_menu_link_type')
      ->setRequired(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Link entity.'))
      ->setReadOnly(TRUE);

    $fields['menu'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Menu'))
      ->setDescription(t('The menu of the Link entity.'))
      ->setSetting('target_type', 'colossal_menu')
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['parent'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Parent'))
      ->setDescription(t('The parent item'))
      ->setSetting('target_type', 'colossal_menu_link')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Menu link title'))
      ->setDescription(t('The text to be used for this link in the menu.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Link machine name'))
      ->setDescription(t('The unique machine name for this menu item.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setPropertyConstraints('value', [
        [
          'UniqueField' => [],
        ],
      ]);

    $fields['show_title'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Show Title'))
      ->setDescription(t('A flag for whether the title should be shown or not.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'boolean',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'settings' => array('display_label' => TRUE),
        'weight' => -4,
      ));

    $fields['link'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Link'))
      ->setDescription(t('The location this menu link points to.'))
      ->setSettings(array(
        'link_type' => LinkItemInterface::LINK_GENERIC,
        'title' => DRUPAL_DISABLED,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'link_default',
        'weight' => -2,
      ));

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('Link weight among links in the same menu at the same depth. In the menu, the links with high weight will sink and links with a low weight will be positioned nearer the top.'))
      ->setDefaultValue(0);

    $fields['enabled'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Enabled'))
      ->setDescription(t('A flag for whether the link should be enabled in menus or hidden.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'boolean',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'settings' => array('display_label' => TRUE),
        'weight' => -1,
      ));

    $fields['parent'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent plugin ID'))
      ->setDescription(t('The ID of the parent menu link plugin, or empty string when at the top level of the hierarchy.'));

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Link entity.'))
      ->setDisplayOptions('form', array(
        'type' => 'language_select',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
