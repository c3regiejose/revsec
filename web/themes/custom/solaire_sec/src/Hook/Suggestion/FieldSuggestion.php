<?php

namespace Drupal\solaire_sec\Hook\Suggestion;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\paragraphs\ParagraphInterface;

class FieldSuggestion {

  /**
   * Implements hook_theme_suggestions_field_alter().
   */
  #[Hook('theme_suggestions_field_alter')]
  public function fieldAlter(array &$suggestions, array $variables): void {
    $paragraph = $variables['element']['#object'] ?? NULL;

    if ($variables['element']['#field_name'] === 'field_referenced_content_items' &&
      $paragraph instanceof ParagraphInterface
    ) {

      if ($paragraph->hasField('field_display_layout') &&
        !$paragraph->get('field_display_layout')->isEmpty()
      ) {        
        $layout = strtr($paragraph->get('field_display_layout')->value, '.', '_');
        $suggestions[] = 'field__paragraph__' . $variables['element']['#bundle'] . '__' . $layout;
      }
    }
  }
}
