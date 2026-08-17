<?php

namespace Drupal\solaire_sec\Hook\Preprocess;

use Drupal\Core\Hook\Attribute\Hook;

class HtmlPreprocess{
  /**
   * @file
   * Functions to support theming.
   */

  /**
   * Implements hook_preprocess_html().
   */
  #[Hook('preprocess_html')]
  public function preprocessHtml(array &$variables): void {
    $variables['dataTheme'] = $this->getTheme();
  }

  /**
   * Get theme from cookie.
   */
  public function getTheme() {
    return 'light';
  }
}
