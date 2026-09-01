<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Paragraph;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\ParagraphHelper;
use Drupal\Core\File\FileUrlGeneratorInterface;

class ImageGalleryVariablesBuilder {
  protected EntityTypeManagerInterface $entityTypeManager;
  protected FileSystemInterface $fileSystem;
  protected FileUrlGeneratorInterface $fileUrlGenerator;
  protected EntityRepositoryInterface $entityRepository;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    FileSystemInterface $file_system,
    FileUrlGeneratorInterface $file_url_generator,
    EntityRepositoryInterface $entity_repository
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
    $this->fileUrlGenerator = $file_url_generator;
    $this->entityRepository = $entity_repository;
  }

  /**
   * Build preprocess variables for the image_gallery paragraph bundle.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The image gallery paragraph entity.
   *
   * @return array
   *   An array of variables for the paragraph template.
   */
  public function buildImageGalleryVariables(ParagraphInterface $paragraph): array {
    $result = [];

    if ($title = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title', $this->entityRepository)) {
      $result['image_gallery_title'] = $title;
    }

    if ($content = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_content', $this->entityRepository)) {
      $result['image_gallery_content'] = $content;
    }

    if ($slideType = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_slide_type', $this->entityRepository)) {
      $result['image_gallery_slide_type'] = $slideType;
    }

    $result['image_gallery_items'] = [];
    if ($paragraph->hasField('field_referenced_content_items') &&
      !$paragraph->get('field_referenced_content_items')->isEmpty()
    ) {
      $ids = array_column(
        $paragraph->get('field_referenced_content_items')->getValue(),
        'target_id'
      );

      $paragraphs = ParagraphHelper::loadParagraphsByIds($this->entityTypeManager, $ids);

      foreach ($paragraphs as $parItem) {
        $item = ParagraphHelper::buildImageItem($parItem, $this->fileUrlGenerator, 'field_mobile_banner');
        if (!empty($item)) {
          $result['image_gallery_items'][] = $item;
        }
      }
    }

    return $result;
  }
}
