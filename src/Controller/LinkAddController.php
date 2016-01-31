<?php

/**
 * @file
 * Contains Drupal\colossal_menu\Controller\LinkAddController.
 */

namespace Drupal\colossal_menu\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class LinkAddController.
 *
 * @package Drupal\colossal_menu\Controller
 */
class LinkAddController extends ControllerBase {
    public function __construct(EntityStorageInterface $storage, EntityStorageInterface $type_storage) {
      $this->storage = $storage;
      $this->typeStorage = $type_storage;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
      /** @var EntityManagerInterface $entity_manager */
      $entity_manager = $container->get('entity.manager');
      return new static(
        $entity_manager->getStorage('colossal_menu_link'),
        $entity_manager->getStorage('colossal_menu_link_type')
      );
    }
    /**
     * Displays add links for available bundles/types for entity colossal_menu_link .
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The current request object.
     *
     * @return array
     *   A render array for a list of the colossal_menu_link bundles/types that can be added or
     *   if there is only one type/bunlde defined for the site, the function returns the add page for that bundle/type.
     */
    public function add(Request $request) {
      $types = $this->typeStorage->loadMultiple();
      if ($types && count($types) == 1) {
        $type = reset($types);
        return $this->addForm($type, $request);
      }
      if (count($types) === 0) {
        return array(
          '#markup' => $this->t('You have not created any %bundle types yet. @link to add a new type.', [
            '%bundle' => 'Link',
            '@link' => $this->l($this->t('Go to the type creation page'), Url::fromRoute('entity.colossal_menu_link_type.add_form')),
          ]),
        );
      }
      return array('#theme' => 'colossal_menu_link_content_add_list', '#content' => $types);
    }

    /**
     * Presents the creation form for colossal_menu_link entities of given bundle/type.
     *
     * @param EntityInterface $colossal_menu_link_type
     *   The custom bundle to add.
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The current request object.
     *
     * @return array
     *   A form array as expected by drupal_render().
     */
    public function addForm(EntityInterface $colossal_menu_link_type, Request $request) {
      $entity = $this->storage->create(array(
        'type' => $colossal_menu_link_type->id()
      ));
      return $this->entityFormBuilder()->getForm($entity);
    }

    /**
     * Provides the page title for this controller.
     *
     * @param EntityInterface $colossal_menu_link_type
     *   The custom bundle/type being added.
     *
     * @return string
     *   The page title.
     */
    public function getAddFormTitle(EntityInterface $colossal_menu_link_type) {
      return t('Create of bundle @label',
        array('@label' => $colossal_menu_link_type->label())
      );
    }

}
