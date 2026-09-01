<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Paragraph;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\ParagraphHelper;

class IconCardsVariablesBuilder {
  protected EntityTypeManagerInterface $entityTypeManager;
  protected EntityRepositoryInterface $entityRepository;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  /**
   * Build preprocess variables for the icon_cards paragraph bundle.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The icon cards paragraph entity.
   *
   * @return array
   *   An array of variables for the paragraph template.
   */
  public function buildIconCardsVariables(ParagraphInterface $paragraph): array {
    $result = [];

    if ($title = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title', $this->entityRepository)) {
      $result['icon_cards_title'] = $title;
    }

    if ($content = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_content', $this->entityRepository)) {
      $result['icon_cards_content'] = $content;
    }

    $result['icon_cards_items'] = [];
    if ($paragraph->hasField('field_referenced_content_items') &&
      !$paragraph->get('field_referenced_content_items')->isEmpty()
    ) {
      $ids = array_column(
        $paragraph->get('field_referenced_content_items')->getValue(),
        'target_id'
      );

      $paragraphsIconCards = ParagraphHelper::loadParagraphsByIds($this->entityTypeManager, $ids);

      foreach ($paragraphsIconCards as $parItem) {
        $card = $this->buildIconCardItem($parItem);
        if (!empty($card)) {
          $result['icon_cards_items'][] = $card;
        }
      }
    }

    return $result;
  }

  /**
   * Build a single icon card item from a referenced paragraph.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The referenced paragraph entity.
   *
   * @return array
   *   A card item array containing heading, content, and icon values.
   */
  protected function buildIconCardItem(ParagraphInterface $paragraph): array {
    $card = [];

    if ($title = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title', $this->entityRepository)) {
      $card['heading'] = $title;
    }

    if ($content = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_content', $this->entityRepository)) {
      $card['content'] = $content;
    }

    if ($icon = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_svg_icon', $this->entityRepository)) {
      $card['icon'] = $icon;
    }

    return $card;
  }
}
