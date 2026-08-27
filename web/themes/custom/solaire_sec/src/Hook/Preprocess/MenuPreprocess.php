<?php

namespace Drupal\solaire_sec\Hook\Preprocess;

use Drupal\Core\Hook\Attribute\Hook;

class MenuPreprocess {
  /**
   * @file
   * Functions to support theming.
   */

  /**
   * Implements hook_preprocess_menu().
   */
  #[Hook('preprocess_menu')]
  public function preprocess(array &$variables): void {
    if ($variables['menu_name'] === 'main') {
      foreach ($variables['items'] as $key => $mainParentMenu) {
        if (!isset($mainParentMenu['below'])) {
          continue;
        }

        $this->mainMenuLevelOne($mainParentMenu['below']);
      }
    }
  }

  /**
   * Level 1
   */
  private function mainMenuLevelOne($menuItems) {
    foreach ($menuItems as $key => $mainMenuLevelOne) {
      // d($mainMenuLevelOne);

      if (isset($mainMenuLevelOne['attributes'])) {
        // d($mainMenuLevelOne['attributes']->getClass()->__toString());
      }
      // die;
    }
  }
}
