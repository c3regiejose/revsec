<?php

namespace Drupal\solaire_sec\Hook\Preprocess;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Container\ContainerInterface;

class ParagraphPreprocess implements ContainerInjectionInterface {
  /**
   * Block manager to create and inspect block plugin instances.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected BlockManagerInterface $blockManager;

  /**
   * Repository to obtain runtime contexts for context-aware plugins.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected ContextRepositoryInterface $contextRepository;

  /**
   * Handler used to apply context mappings to plugins.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected ContextHandlerInterface $contextHandler;

  /**
   * Entity type manager for loading paragraph entities.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * ParagraphPreprocess constructor.
   */
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

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('plugin.manager.block'),
      $container->get('context.repository'),
      $container->get('context.handler'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Implements hook_preprocess_paragraph().
   */
  #[Hook('preprocess_paragraph')]
  public function preprocess(array &$variables): void {
    $paragraph = $variables['elements']['#paragraph'] ?? NULL;
    
    if (!$paragraph instanceof ParagraphInterface) {
      return;
    }
    $new_variables = $this->buildVariablesFromParagraph($paragraph);
    $variables = array_merge($variables, $new_variables);
  }

  /**
   * Build a variables array from a Paragraph entity.
   *
   * Separated from hook_preprocess for easier unit testing.
   */
  public function buildVariablesFromParagraph(ParagraphInterface $paragraph): array {
    $variables = [];

    // Text Alignment
    if ($paragraph->hasField('field_text_alignment') &&
      !$paragraph->get('field_text_alignment')->isEmpty()
    ) {
      $variables['textAlignment'] = 'text-align-' . $paragraph->get('field_text_alignment')->value;
    }

    // Slide per view
    if ($paragraph->hasField('field_slide_per_view') &&
      !$paragraph->get('field_slide_per_view')->isEmpty()
    ) {
      $variables['slidesPerView'] = (int) $paragraph->get('field_slide_per_view')->value;
    }

    // Tabs Content by View.
    if ($paragraph->bundle() === 'tabs_content_by_view') {
      $variables = array_merge($variables, $this->buildTabsContentByViewVariables($paragraph));
    }

    if ($paragraph->bundle() === 'accordion') {
      $variables = array_merge($variables, $this->buildAccordionVariables($paragraph));
    }

    return $variables;
  }

  /**
   * Builds variables specific to the "accordion" paragraph bundle.
   */
  protected function buildAccordionVariables(ParagraphInterface $paragraph): array {
    $result = [];

    if ($paragraph->hasField('field_title') &&
      !$paragraph->get('field_title')->isEmpty()
    ) {
      $result['accordion_title'] = $paragraph->get('field_title')->value;
    }

    if ($paragraph->hasField('field_content') &&
      !$paragraph->get('field_content')->isEmpty()
    ) {
      $result['accordion_content'] = $paragraph->get('field_content')->value;
    }

    if ($paragraph->hasField('field_referenced_content_items') &&
      !$paragraph->get('field_referenced_content_items')->isEmpty()
    ) {
      $ids = array_column(
        $paragraph->get('field_referenced_content_items')->getValue(),
        'target_id'
      );

      $paragraphsAccordion = $this->loadParagraphsByIds($ids);

      foreach ($paragraphsAccordion as $parItem) {
        if ($parItem->hasField('field_title') &&
          !$parItem->get('field_title')->isEmpty()
        ) {
          $result['accordion_items'][$parItem->id()]['heading'] = $parItem->get('field_title')->value;
        }

        if ($parItem->hasField('field_content') &&
          !$parItem->get('field_content')->isEmpty()
        ) {
          $result['accordion_items'][$parItem->id()]['content'] = $parItem->get('field_content')->value;
        }
      }
    }
    return $result;
  }

  /**
   * Build variables specific to the "tabs_content_by_view" paragraph bundle.
   */
  protected function buildTabsContentByViewVariables(ParagraphInterface $paragraph): array {
    $result = [];

    if ($paragraph->hasField('field_text_alignment') && 
      !$paragraph->get('field_text_alignment')->isEmpty()
    ) {
      $result['textAlignment'] = 'text-align-' . $paragraph->get('field_text_alignment')->value;
    }

    if ($paragraph->hasField('field_title') &&
      !$paragraph->get('field_title')->isEmpty()
    ) {
      $result['tab_content_title'] = $paragraph->get('field_title')->value;
    }

    if ($paragraph->hasField('field_content') &&
      !$paragraph->get('field_content')->isEmpty()
    ) {
      $result['tab_content_content'] = $paragraph->get('field_content')->value;
    }

    if ($paragraph->hasField('field_referenced_content_items') &&
      !$paragraph->get('field_referenced_content_items')->isEmpty()
    ) {
      $ids = array_column(
        $paragraph->get('field_referenced_content_items')->getValue(),
        'target_id'
      );

      $paragraphs = $this->loadParagraphsByIds($ids);

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
          $items = $this->renderBlockField($parItem, 'field_views_block');
          $result['tab_content_items'][$parItem->id()]['views_block'] = $items;
        }
      }
    }

    return $result;
  }

  /**
   * Loads paragraph entities by ID, preserving the given order.
   *
   * @param int[] $ids
   *   Paragraph entity IDs.
   *
   * @return \Drupal\paragraphs\ParagraphInterface[]
   *   Loaded paragraphs in the same order as the provided IDs.
   */
  protected function loadParagraphsByIds(array $ids): array {
    $ids = array_values(array_filter($ids));
    if (!$ids) {
      return [];
    }

    $loaded = $this->entityTypeManager
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
   * Renders a block_field as a themed block render array.
   */
  protected function renderBlockField(ParagraphInterface $paragraph, string $field_name): array {
    $item = $paragraph->get($field_name)->first();
    if (!$item) {
      return [];
    }

    $plugin_id = $item->plugin_id ?? $item->value;
    $settings = $item->settings ?? [];

    if (empty($plugin_id)) {
      return [];
    }

    return $this->renderBlock($plugin_id, is_array($settings) ? $settings : []);
  }

  /**
   * Builds a render array for a block plugin.
   */
  protected function renderBlock(string $plugin_id, array $configuration = []): array {
    $block_manager = $this->blockManager;
    if (!$block_manager->hasDefinition($plugin_id)) {
      return [];
    }

    $plugin_block = $block_manager->createInstance($plugin_id, $configuration);

    if ($plugin_block instanceof ContextAwarePluginInterface) {
      $contexts = $this->contextRepository
        ->getRuntimeContexts($plugin_block->getContextMapping());
      $this->contextHandler
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
