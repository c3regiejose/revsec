<?php

namespace Drupal\solaire_sec\Hook\Preprocess;

use Drupal\node\NodeInterface;
use Drupal\Core\Hook\Attribute\Hook;

class PagePreprocess{
  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocess(array &$variables): void {
    // $variables['#attached']['library'][] = 'solaire_sec/swiper';
  }
}
