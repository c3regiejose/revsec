<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Paragraph;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\ContextAwarePluginInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\media\Entity\MediaType;

class ParagraphHelper {
  /**
   * Returns the raw string value of a paragraph field, or NULL if missing.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph entity.
   * @param string $fieldName
   *   The field machine name.
   *
   * @return string|null
   *   The field value or NULL when the field is absent or empty.
   */
  public static function getParagraphFieldValue(
    ParagraphInterface $paragraph, 
    string $fieldName,
    ?EntityRepositoryInterface $entity_repository = NULL
  ): ?string {
    if ($entity_repository) {
      $paragraph = $entity_repository->getTranslationFromContext($paragraph);
    }

    if ($paragraph->hasField($fieldName) && !$paragraph->get($fieldName)->isEmpty()) {
      return $paragraph->get($fieldName)->value;
    }

    return '';
  }

  /**
   * Returns the raw string value of a paragraph field, or NULL if missing.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph entity.
   * @param string $fieldName
   *   The field machine name.
   *
   * @return string|null
   *   The field value or NULL when the field is absent or empty.
   */
  public static function getParagraphReferenceFieldValue(ParagraphInterface $paragraph, string $fieldName): ?array {
    if ($paragraph->hasField($fieldName) && !$paragraph->get($fieldName)->isEmpty()) {
      return array_column(
        $paragraph->get($fieldName)->getValue(),
        'target_id'
      );
    }
    return [];
  }

  /**
   * Load paragraph entities by ID while preserving the provided order.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param int[] $ids
   *   The paragraph IDs to load.
   *
   * @return \Drupal\paragraphs\ParagraphInterface[]
   *   The loaded paragraph entities in the same order as the input IDs.
   */
  public static function loadParagraphsByIds(EntityTypeManagerInterface $entity_type_manager, array $ids): array {
    $ids = array_values(array_filter($ids));
    if (!$ids) {
      return [];
    }

    $loaded = $entity_type_manager
      ->getStorage('paragraph')
      ->loadMultiple($ids);

    $paragraphs = [];
    foreach ($ids as $id) {
      if (isset($loaded[$id])) {
        $paragraphs[] = $loaded[$id];
      }
    }

    return $paragraphs;
  }

  /**
   * Render a paragraph block field as a themed block render array.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph entity containing the field.
   * @param string $field_name
   *   The block field machine name.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager service.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The context repository service.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The context handler service.
   *
   * @return array
   *   A render array for the block plugin, or an empty array if the block
   *   cannot be built.
   */
  public static function renderBlockField(
    ParagraphInterface $paragraph,
    string $field_name,
    BlockManagerInterface $block_manager,
    ContextRepositoryInterface $context_repository,
    ContextHandlerInterface $context_handler
  ): array {
    $item = $paragraph->get($field_name)->first();
    if (!$item) {
      return [];
    }

    $plugin_id = $item->plugin_id ?? $item->value;
    $settings = $item->settings ?? [];

    if (empty($plugin_id)) {
      return [];
    }

    return self::renderBlock(
      $plugin_id,
      is_array($settings) ? $settings : [],
      $block_manager,
      $context_repository,
      $context_handler
    );
  }

  /**
   * Create a themed render array for a block plugin.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   * @param array $configuration
   *   The plugin configuration.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager service.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The context repository service.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The context handler service.
   *
   * @return array
   *   A render array for the block plugin, or an empty array if the plugin
   *   cannot be built.
   */
  private static function renderBlock(
    string $plugin_id,
    array $configuration,
    BlockManagerInterface $block_manager,
    ContextRepositoryInterface $context_repository,
    ContextHandlerInterface $context_handler
  ): array {
    if (!$block_manager->hasDefinition($plugin_id)) {
      return [];
    }

    $plugin_block = $block_manager->createInstance($plugin_id, $configuration);

    if ($plugin_block instanceof ContextAwarePluginInterface) {
      $contexts = $context_repository
        ->getRuntimeContexts($plugin_block->getContextMapping());
      $context_handler
        ->applyContextMapping($plugin_block, $contexts);
    }

    $build = $plugin_block->build();
    if (empty($build)) {
      return [];
    }

    return [
      '#theme' => 'block',
      '#configuration' => $plugin_block->getConfiguration(),
      '#plugin_id' => $plugin_block->getPluginId(),
      '#base_plugin_id' => $plugin_block->getBaseId(),
      '#derivative_plugin_id' => $plugin_block->getDerivativeId(),
      'content' => $build,
    ];
  }

  /**
   * Build an image gallery item from a paragraph.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph containing image fields.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator service.
   * @param string $field_name
   *   The field machine name.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface|null $entity_repository
   *   Optional. The entity repository service for translation context.
   *
   * @return array
   *   An associative array with image data, or an empty array when none found.
   */
  public static function buildImageItem(
    ParagraphInterface $paragraph, 
    FileUrlGeneratorInterface $file_url_generator,
    $field_name,
    ?EntityRepositoryInterface $entity_repository = NULL
  ): array {
    $item = [];

    if ($entity_repository) {
      $paragraph = $entity_repository->getTranslationFromContext($paragraph);
    }

    if (!($paragraph->hasField($field_name) && !$paragraph->get($field_name)->isEmpty())) {
      return [];
    }

    $entity = $paragraph->get($field_name)->entity ?? NULL;
    $uri = NULL;

    if ($entity) {
      if ($entity instanceof \Drupal\file\Entity\File) {
        $uri = $file_url_generator->generateAbsoluteString($entity->getFileUri());
      }
    }

    if ($uri) {
      if (function_exists('file_create_url')) {
        $item['image_url'] = file_create_url($uri);
      }
      else {
        $item['image_uri'] = $uri;
      }
    }

    $item['image_alt'] = $paragraph->get($field_name)->alt ?? '';
    $item['id'] = $entity->id();

    return $item;
  }

  /**
   * Returns the absolute URL for a video file referenced by a paragraph.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph containing the media reference field.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator service.
   * @param string $field_name
   *   The paragraph field containing the video media reference.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface|null $entity_repository
   *   Optional. The entity repository service for translation context.
   *
   * @return string|null
   *   The absolute video file URL, or NULL when no video file is found.
   */
  public static function getVideoFileUrl(
    ParagraphInterface $paragraph,
    FileUrlGeneratorInterface $file_url_generator,
    string $field_name,
    ?EntityRepositoryInterface $entity_repository = NULL
  ): ?string {
    if ($entity_repository) {
      $paragraph = $entity_repository->getTranslationFromContext($paragraph);
    }

    if (!$paragraph->hasField($field_name) || $paragraph->get($field_name)->isEmpty()) {
      return NULL;
    }

    $media = $paragraph->get($field_name)->entity;
    if (!$media instanceof \Drupal\media\MediaInterface) {
      return NULL;
    }

    $media_type = MediaType::load($media->bundle());
    if (!$media_type) {
      return NULL;
    }

    $source_field = $media->getSource()->getSourceFieldDefinition($media_type)->getName();
    if (!$media->hasField($source_field) || $media->get($source_field)->isEmpty()) {
      return NULL;
    }

    $file = $media->get($source_field)->entity;
    if (!$file instanceof \Drupal\file\FileInterface) {
      return NULL;
    }

    return $file_url_generator->generateAbsoluteString($file->getFileUri());
  }
}
