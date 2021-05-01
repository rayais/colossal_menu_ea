<?php

namespace Drupal\colossal_menu\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Url;
use Drupal\system\MenuInterface;
use Drupal\colossal_menu\LinkInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class LinkAddController.
 *
 * @package Drupal\colossal_menu\Controller
 */
class LinkController extends ControllerBase {

  /**
   * Current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Colossal Menu Link storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Colossal Menu Link Type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $typeStorage;

  /**
   * LinkController constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   Colossal Menu Link storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $type_storage
   *   Colossal Menu Link Type storage.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Current route match.
   */
  public function __construct(
    EntityStorageInterface $storage,
    EntityStorageInterface $type_storage,
    RouteMatchInterface $route_match
  ) {
    $this->storage = $storage;
    $this->typeStorage = $type_storage;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $entity_type_manager->getStorage('colossal_menu_link'),
      $entity_type_manager->getStorage('colossal_menu_link_type'),
      $container->get('current_route_match')
    );
  }

  /**
   * Add new Link page.
   *
   * Displays add links for available bundles/types for entity
   * colossal_menu_link.
   *
   * @param \Drupal\system\MenuInterface $colossal_menu
   *   An entity representing a custom menu.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return array
   *   A render array for a list of the colossal_menu_link bundles/types that
   *   can be added or if there is only one type/bunlde defined for the site,
   *   the function returns the add page for that bundle/type.
   */
  public function add(MenuInterface $colossal_menu, Request $request) {
    $types = $this->typeStorage->loadMultiple();
    if ($types && count($types) == 1) {
      $type = reset($types);
      return $this->addForm($colossal_menu, $type, $request);
    }
    if (count($types) === 0) {
      return [
        '#markup' => $this->t('You have not created any %bundle types yet. @link to add a new type.', [
          '%bundle' => 'Link',
          '@link' => Link::fromTextAndUrl($this->t('Go to the type creation page'), Url::fromRoute('entity.colossal_menu_link_type.add_form')),
        ]),
      ];
    }

    $links = [];
    foreach ($types as $type) {
      $params = [
        'colossal_menu_link_type' => $type->id(),
        'colossal_menu' => $this->routeMatch->getParameter('colossal_menu')->id(),
      ];
      $options = [
        'query' => $request->query->all(),
      ];
      $url = new Url('entity.colossal_menu_link.add_form', $params, $options);
      $links[$type->id()] = [
        'url' => $url,
        'title' => $type->label(),
      ];
    }

    return [
      '#theme' => 'links',
      '#links' => $links,
      '#attributes' => [
        'class' => [
          'admin-list',
        ],
      ],
    ];
  }

  /**
   * Add new Link form.
   *
   * Presents the creation form for colossal_menu_link entities of given
   * bundle/type.
   *
   * @param \Drupal\system\MenuInterface $colossal_menu
   *   An entity representing a custom menu.
   * @param \Drupal\Core\Entity\EntityInterface $colossal_menu_link_type
   *   The custom bundle to add.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function addForm(MenuInterface $colossal_menu,
                          EntityInterface $colossal_menu_link_type,
                          Request $request) {
    $entity = $this->storage->create([
      'type' => $colossal_menu_link_type->id(),
      'menu' => $colossal_menu,
    ]);
    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Provides the page title for this controller.
   *
   * @param \Drupal\Core\Entity\EntityInterface $colossal_menu_link_type
   *   The custom bundle/type being added.
   *
   * @return string
   *   The page title.
   */
  public function getAddFormTitle(EntityInterface $colossal_menu_link_type) {
    return $this->t('Create of bundle @label', [
      '@label' => $colossal_menu_link_type->label(),
    ]);
  }

  /**
   * Edit Link form.
   *
   * Presents the creation form for colossal_menu_link entities of given
   * bundle/type.
   *
   * @param \Drupal\colossal_menu\LinkInterface $colossal_menu_link
   *   The custom bundle to add.
   *
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function editForm(LinkInterface $colossal_menu_link) {
    return $this->entityFormBuilder()->getForm($colossal_menu_link);
  }

  /**
   * Provides the page title for this controller.
   *
   * @param \Drupal\colossal_menu\LinkInterface $colossal_menu_link
   *   Link type being edited.
   *
   * @return string
   *   The page title.
   */
  public function getEditFormTitle(LinkInterface $colossal_menu_link) {
    return $this->t('Edit @label', [
      '@label' => $colossal_menu_link->label(),
    ]);
  }

}
