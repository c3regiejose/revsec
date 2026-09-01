<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Paragraph;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\ParagraphHelper;

class TestimonialCardsVariablesBuilder {
  protected EntityTypeManagerInterface $entityTypeManager;
  protected EntityRepositoryInterface $entityRepository;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  public function buildTestimonialCardsVariables(ParagraphInterface $paragraph): array {
    $result = [];

    if ($title = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title', $this->entityRepository)) {
      $result['testimonial_cards_title'] = $title;
    }

    if ($content = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_content', $this->entityRepository)) {
      $result['testimonial_cards_content'] = $content;
    }

    $result['testimonial_cards_items'] = [];
    if ($paragraph->hasField('field_referenced_content_items') &&
      !$paragraph->get('field_referenced_content_items')->isEmpty()
    ) {
      $ids = array_column(
        $paragraph->get('field_referenced_content_items')->getValue(),
        'target_id'
      );

      $paragraphs = ParagraphHelper::loadParagraphsByIds($this->entityTypeManager, $ids);
      foreach ($paragraphs as $parItem) {
        $testimonial = $this->buildTestimonialCardItem($parItem);
        if (!empty($testimonial)) {
          $result['testimonial_cards_items'][] = $testimonial;
        }
      }
    }

    return $result;
  }

  protected function buildTestimonialCardItem(ParagraphInterface $paragraph): array {
    $item = [];

    if ($heading = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title', $this->entityRepository)) {
      $item['heading'] = $heading;
    }

    if ($quote = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_content', $this->entityRepository)) {
      $item['quote'] = $quote;
    }

    if ($customerName = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_customer_name', $this->entityRepository)) {
      $item['customer_name'] = $customerName;
    }

    if ($date = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_date', $this->entityRepository)) {
      $item['date'] = $date;
    }

    if ($rating = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_rating', $this->entityRepository)) {
      $item['rating'] = $rating;
    }

    return $item;
  }
}
