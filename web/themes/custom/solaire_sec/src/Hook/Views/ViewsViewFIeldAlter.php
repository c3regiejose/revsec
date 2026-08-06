<?php

namespace Drupal\solaire_sec\Hook\Views;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\views\ViewExecutable;

class ViewsViewFieldAlter {
  /**
   * Implements hook_theme_suggestions_views_view_fields_alter().
   */
  #[Hook('theme_suggestions_views_view_fields_alter')]
  public function viewsViewFieldAlter(array &$suggestions, array $variables): void {
      /** @var \Drupal\views\ViewExecutable $view */
      $view = $variables['view'];

      // Suggestion by view ID.
      $suggestions[] = 'views_view_fields__' . $view->id();

      // Suggestion by view ID and display ID.
      $suggestions[] = 'views_view_fields__' . $view->id() . '__' . $view->current_display;

      // Suggestion by view ID, display ID, and style plugin.
      $suggestions[] = 'views_view_fields__' . $view->id() . '__' . $view->current_display . '__' . $view->style_plugin->getPluginId();
  }

  /**
   * Implements hook_theme_suggestions_views_view_unformatted_alter().
   */
  #[Hook('theme_suggestions_views_view_unformatted_alter')]
  public function viewsViewUnformattedAlter(array &$suggestions, array $variables): void {
    /** @var \Drupal\views\ViewExecutable $view */
    $view = $variables['view'];

    if ($view->id() === 'solaire_frontpage' && $view->current_display === 'block_1') {
      $suggestions[] = 'views_view_unformatted__homepage_banner__hero';
    }

    if ($view->id() == 'offers' && $view->getDisplay()->getPluginDefinition()['id'] == 'block') {
      $suggestions[] = 'views_view_unformatted__offer_block';
    }
  }

  /**
   * Implements hook_theme_suggestions_views_view_alter().
   */
  #[Hook('theme_suggestions_views_view_alter')]
  public function viewsViewAlter(array &$suggestions, array $variables): void {
    /** @var \Drupal\views\ViewExecutable $view */
    $view = $variables['view'];

    // Suggestion by view ID.
    $suggestions[] = 'views_view__' . $view->id();

    // Suggestion by view ID and display ID.
    $suggestions[] = 'views_view__' . $view->id() . '__' . $view->current_display;
  }
}
