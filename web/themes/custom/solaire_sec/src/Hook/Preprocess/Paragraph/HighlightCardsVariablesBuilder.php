<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Paragraph;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\ParagraphHelper;

class HighlightCardsVariablesBuilder {
  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Build preprocess variables for the highlight_cards paragraph bundle.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The highlight cards paragraph entity.
   *
   * @return array
   *   An array of variables for the paragraph template.
   */
  public function buildHighlightCardsVariables(ParagraphInterface $paragraph): array {
    $result = [];

    if ($title = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title')) {
      $result['highlight_cards_title'] = $title;
    }

    if ($content = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_content')) {
      $result['highlight_cards_content'] = $content;
    }

    $result['highlight_cards_items'] = [];
    if ($paragraph->hasField('field_referenced_content_items') &&
      !$paragraph->get('field_referenced_content_items')->isEmpty()
    ) {
      $ids = array_column(
        $paragraph->get('field_referenced_content_items')->getValue(),
        'target_id'
      );

      $paragraphs = ParagraphHelper::loadParagraphsByIds($this->entityTypeManager, $ids);
      foreach ($paragraphs as $parItem) {
        $item = $this->buildHighlightCardItem($parItem);
        if (!empty($item)) {
          $result['highlight_cards_items'][] = $item;
        }
      }
    }

    return $result;
  }

  protected function buildHighlightCardItem(ParagraphInterface $paragraph): array {
    $item = [];

    if ($heading = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title')) {
      $item['heading'] = $heading;
    }

    if ($content = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_content')) {
      $item['content'] = $content;
    }

    return $item;
  }
}
