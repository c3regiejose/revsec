<?php

namespace Drupal\solaire_sec\Hook\Preprocess;

use Drupal\node\NodeInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\solaire_sec\Hook\Preprocess\Page\BannerVariablesBuilder;
use Psr\Container\ContainerInterface;

class PagePreprocess implements ContainerInjectionInterface {

  /**
   * Entity type manager for loading paragraph entities.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
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
  protected FileSystemInterface $fileSystem;
  protected FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * ParagraphPreprocess constructor.
   */
  public function __construct(
    BlockManagerInterface $block_manager,
    ContextRepositoryInterface $context_repository,
    ContextHandlerInterface $context_handler,
    EntityTypeManagerInterface $entity_type_manager,
    FileSystemInterface $file_system,
    FileUrlGeneratorInterface $file_url_generator
  ) {
    $this->blockManager = $block_manager;
    $this->contextRepository = $context_repository;
    $this->contextHandler = $context_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('plugin.manager.block'),
      $container->get('context.repository'),
      $container->get('context.handler'),
      $container->get('entity_type.manager'),
      $container->get('file_system'),
      $container->get('file_url_generator')
    );
  }

  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocess(array &$variables): void {
    if (isset($variables['node']) && $variables['node'] instanceof NodeInterface) {

      $node = $variables['node'];

      $builder = new BannerVariablesBuilder(
        $this->entityTypeManager,
        $this->fileSystem,
        $this->fileUrlGenerator,
        $variables['is_front']
      );
      $variables = array_merge($variables, $builder->buildBannerVariables($node));

      $variables['hide_banner'] = false;
      if ($node->hasField('field_hide_banner') && !$node->get('field_hide_banner')->isEmpty()) {
        $variables['hide_banner'] = ($node->get('field_hide_banner')->value == "1") ? true : false;
      }
    }
  }
}
