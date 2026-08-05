<?php

namespace Drupal\solaire_sec\Hook\Preprocess;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\paragraphs\ParagraphInterface;

class ParagraphPreprocess {
  /**
   * Implements hook_preprocess_paragraph().
   */
  #[Hook('preprocess_paragraph')]
  public function preprocess(array &$variables): void {
    $paragraph = $variables['elements']['#paragraph'] ?? NULL;
    
    if (!$paragraph instanceof ParagraphInterface) {
      return;
    }

    // Text Alignment
    if ($paragraph->hasField('field_text_alignment') &&
      !$paragraph->get('field_text_alignment')->isEmpty()
    ) {
      $variables['textALignment'] = 'text-align-' . $paragraph->get('field_text_alignment')->value;
    }

    // Slide per view
    if ($paragraph->hasField('field_slide_per_view') &&
      !$paragraph->get('field_slide_per_view')->isEmpty()
    ) {
      $variables['slidesPerView'] = (int) $paragraph->get('field_slide_per_view')->value;
    }

    // Main Banner
    // if ($paragraph->bundle() === 'main_banner' &&
    //   $paragraph->hasField('field_banner_items') &&
    //   !$paragraph->get('field_banner_items')->isEmpty()
    // ) {
    //   d($paragraph->get('field_banner_items'));
    // }
  }

}
