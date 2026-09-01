<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Paragraph;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\ParagraphHelper;

class AccordionVariablesBuilder {
  protected EntityTypeManagerInterface $entityTypeManager;
  protected EntityRepositoryInterface $entityRepository;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  public function buildAccordionVariables(ParagraphInterface $paragraph): array {
    $result = [];

    if ($title = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title', $this->entityRepository)) {
      $result['accordion_title'] = $title;
    }

    if ($content = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_content', $this->entityRepository)) {
      $result['accordion_content'] = $content;
    }

    if ($paragraph->hasField('field_referenced_content_items') &&
      !$paragraph->get('field_referenced_content_items')->isEmpty()
    ) {
      $ids = array_column(
        $paragraph->get('field_referenced_content_items')->getValue(),
        'target_id'
      );

      $paragraphsAccordion = ParagraphHelper::loadParagraphsByIds($this->entityTypeManager, $ids);

      foreach ($paragraphsAccordion as $parItem) {

        if ($title = ParagraphHelper::getParagraphFieldValue($parItem, 'field_title', $this->entityRepository)) {
          $result['accordion_items'][$parItem->id()]['heading'] = $title;
        }

        if ($content = ParagraphHelper::getParagraphFieldValue($parItem, 'field_content', $this->entityRepository)) {
          $result['accordion_items'][$parItem->id()]['content'] = $content;
        }
      }
    }

    return $result;
  }

}
