<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Paragraph;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\ContextAwarePluginInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\paragraphs\ParagraphInterface;

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
  public static function getParagraphFieldValue(ParagraphInterface $paragraph, string $fieldName): ?string {
    if ($paragraph->hasField($fieldName) && !$paragraph->get($fieldName)->isEmpty()) {
      return $paragraph->get($fieldName)->value;
    }
    return '';
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
}
