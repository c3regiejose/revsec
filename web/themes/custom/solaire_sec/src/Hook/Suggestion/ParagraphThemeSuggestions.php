<?php

namespace Drupal\solaire_sec\Hook\Suggestion;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Hook implementations for solaire_sec.
 */
class ParagraphThemeSuggestions {
  /**
   * Implements hook_preprocess_node().
   */
  #[Hook('theme_suggestions_paragraph_alter')]
  public function paragraphAlter(array &$suggestions, array $variables): void {
    $paragraph = $variables['elements']['#paragraph'] ?? NULL;
    
    if (!$paragraph instanceof ParagraphInterface) {
      return;
    }

    // Add a template suggestion for the paragraph bundle.
    $suggestions[] = 'paragraph__' . $paragraph->bundle();

    // Add a template suggestion for the bundle and view mode.
    $view_mode = $variables['elements']['#view_mode'] ?? NULL;
    if ($view_mode) {
      $suggestions[] = sprintf(
        'paragraph__%s__%s',
        $paragraph->bundle(),
        strtr($view_mode, '.', '_'),
      );
    }

    // Add separate template suggestion for slider.
    // if ($paragraph->hasField('field_is_slider') &&
    //   !$paragraph->get('field_is_slider')->isEmpty()
    // ) {
    //   if ($paragraph->get('field_is_slider')->value) {
    //     $suggestions[] = 'paragraph__' . $paragraph->bundle() . '__slider';

    //     if ($view_mode) {
    //       $vMode = strtr($view_mode, '.', '_');
    //       $suggestions[] = 'paragraph__' . $paragraph->bundle() . '__' . $vMode .'__slider';
    //     }
    //   }
    // }
  }
}
