<?php

namespace Drupal\solaire_sec\Hook\Preprocess;

use Drupal\node\NodeInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for solaire_sec.
 */
class NodeOffers {
  /**
   * @file
   * Functions to support theming.
   */

  /**
   * Implements hook_preprocess_node().
   */
  #[Hook('preprocess_node')]
  public function preprocess(array &$variables): void {
    $node = $variables['node'] ?? NULL;

    if (!$node instanceof NodeInterface) {
      return;
    }

    if (!empty($variables['view_mode']) && $variables['view_mode'] == 'card') {
      $variables['card'] = $this->preprocessNodeOffers($node, $variables);
    }
  }

  /**
   * Offers preprocess
   */
  public function preprocessNodeOffers($node, $variables) {

    // view mode card.
    if (!empty($variables['elements']['#view_mode']) &&
      $variables['elements']['#view_mode'] === 'card'
    ) {
      return $this->nodeCardView($node);
    }

  }

  /**
   * View mode Card
   */
  public function nodeCardView($node) {
    $bannerImage = NULL;
    if ($node->hasField('field_mobile_thumbnail') && !$node->get('field_mobile_thumbnail')->isEmpty()) {
      $bannerImage = $node->get('field_mobile_thumbnail')->view('card');
    }

    $teaserSummary = NULL;
    if ($node->hasField('field_teaser_summary') && !$node->get('field_teaser_summary')->isEmpty()) {
      $teaserSummary = $node->get('field_teaser_summary')->view('card');
    }

    return [
      'image' => $bannerImage,
      'body' => $teaserSummary,
    ];
  }
}
