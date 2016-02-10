<?php

/**
 * @file
 * Contains colossal_menu_link.page.inc..
 *
 * Page callback for Link entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Link templates.
 *
 * Default template: colossal_menu_link.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_colossal_menu_link(array &$variables) {
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

/**
 * Prepares variables for a custom entity type creation list templates.
 *
 * Default template: colossal_menu_link-content-add-list.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - content: An array of colossal_menu_link-types.
 *
 * @see block_content_add_page()
 */
function template_preprocess_colossal_menu_link_content_add_list(&$variables) {
  $variables['types'] = array();
  $query = \Drupal::request()->query->all();
  foreach ($variables['content'] as $type) {
    $variables['types'][$type->id()] = [
      'link' => Link::fromTextAndUrl($type->label(), new Url('entity.colossal_menu_link.add_form', [
        'colossal_menu_link_type' => $type->id(),
        'colossal_menu' => \Drupal::routeMatch()->getParameter('colossal_menu')->id(),
      ],
      [
        'query' => $query,
      ])),
      'description' => [
        '#markup' => $type->label(),
      ],
      'title' => $type->label(),
      'localized_options' => [
        'query' => $query,
      ],
    ];
  }
}
