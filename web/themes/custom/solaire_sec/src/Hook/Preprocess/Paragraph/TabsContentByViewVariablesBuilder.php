<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Paragraph;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\ParagraphHelper;

class TabsContentByViewVariablesBuilder {
  protected BlockManagerInterface $blockManager;

  protected ContextRepositoryInterface $contextRepository;

  protected ContextHandlerInterface $contextHandler;

  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(
    BlockManagerInterface $block_manager,
    ContextRepositoryInterface $context_repository,
    ContextHandlerInterface $context_handler,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->blockManager = $block_manager;
    $this->contextRepository = $context_repository;
    $this->contextHandler = $context_handler;
    $this->entityTypeManager = $entity_type_manager;
  }

  public function buildTabsContentByViewVariables(ParagraphInterface $paragraph): array {
    $result = [];

    if ($title = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title')) {
      $result['tab_content_title'] = $title;
    }

    if ($content = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_content')) {
      $result['tab_content_content'] = $content;
    }

    if ($paragraph->hasField('field_referenced_content_items') &&
      !$paragraph->get('field_referenced_content_items')->isEmpty()
    ) {
      $ids = array_column(
        $paragraph->get('field_referenced_content_items')->getValue(),
        'target_id'
      );

      $paragraphs = ParagraphHelper::loadParagraphsByIds($this->entityTypeManager, $ids);

      foreach ($paragraphs as $parItem) {
        if ($parItem->hasField('field_title') &&
          !$parItem->get('field_title')->isEmpty()
        ) {
          $result['tab_content_items'][$parItem->id()]['heading'] = $parItem->get('field_title')->value;
          $result['tab_content_items'][$parItem->id()]['heading_slug'] = str_replace(
            ' ',
            '-',
            strtolower($parItem->get('field_title')->value)
          );
        }

        if ($parItem->hasField('field_views_block') &&
          !$parItem->get('field_views_block')->isEmpty()
        ) {
          $items = ParagraphHelper::renderBlockField(
            $parItem,
            'field_views_block',
            $this->blockManager,
            $this->contextRepository,
            $this->contextHandler
          );
          $result['tab_content_items'][$parItem->id()]['views_block'] = $items;
        }
      }
    }

    return $result;
  }
}
