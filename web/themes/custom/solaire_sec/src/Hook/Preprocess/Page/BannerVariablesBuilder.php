<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Page;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\ParagraphHelper;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;

class BannerVariablesBuilder {

	protected EntityTypeManagerInterface $entityTypeManager;
	protected FileSystemInterface $fileSystem;
	protected FileUrlGeneratorInterface $fileUrlGenerator;
	protected $isFront;
  protected EntityRepositoryInterface $entityRepository;

	public function __construct(
		EntityTypeManagerInterface $entity_type_manager,
		FileSystemInterface $file_system,
		FileUrlGeneratorInterface $file_url_generator,
    EntityRepositoryInterface $entity_repository,
		$is_front
	) {
		$this->entityTypeManager = $entity_type_manager;
		$this->fileSystem = $file_system;
		$this->fileUrlGenerator = $file_url_generator;
		$this->entityRepository = $entity_repository;
		$this->isFront = $is_front;
	}

	/**
	 * Build banner variables from a node.
	 */
	public function buildBannerVariables(NodeInterface $node): array {

    $pageBanner = [];

    if ($this->isFront) {
      $nodes = $this->getAllBannerFeaturedInFrontPage();
      $pageBanner['page_banner'] = $this->getBannersItems($nodes);
    } else {
      $nodes[$node->id()] = $node;
      $pageBanner['page_banner'] = $this->getBannersItems($nodes);
    }

		return $pageBanner;
	}

  /**
   * 
   */
  public function getBannersItems($nodes) {
    $bannerCollectionItems = [];

    foreach ($nodes as $node) {
      if ($node->hasField('field_banner') &&
        !$node->get('field_banner')->isEmpty()
      ) {
        $ids = array_column(
          $node->get('field_banner')->getValue(),
          'target_id'
        );

        $paragraphs = ParagraphHelper::loadParagraphsByIds($this->entityTypeManager, $ids, $this->entityRepository);
        foreach ($paragraphs as $paragraph) {
          if ($paragraph->hasField('field_banner_items') &&
            !$paragraph->get('field_banner_items')->isEmpty()
          ) {
            $parIds = array_column(
              $paragraph->get('field_banner_items')->getValue(),
              'target_id'
            );

            // Load paragraph banner items
            $bannerCollectionItems[$paragraph->id()] = $this->loadParagrapgBannersItem($parIds);
          }
        }
      }
    }

    return $this->processBanner($bannerCollectionItems);
  }

  /**
   * Process banner.
   */
  public function processBanner($bannerCollections) {
    $result = [];

    if ($bannerCollections) {
      foreach ($bannerCollections as $banners) {
        foreach ($banners as $key => $banner) {
          $result[$key] = $banner;
        }
      }
    }

    return $result;
  }

  /**
   * Load paragraph banners
   */
  public function loadParagrapgBannersItem($parIds) {
    $bannerLists = [];
    $bannerItems = ParagraphHelper::loadParagraphsByIds($this->entityTypeManager, $parIds, $this->entityRepository);

    foreach ($bannerItems as $banner) {
      $desktopBanner = ParagraphHelper::buildImageItem(
        $banner, 
        $this->fileUrlGenerator, 
        'field_image', 
        $this->entityRepository
      );
      if (!empty($desktopBanner)) {
        $bannerLists[$banner->id()]['field_image'] = $desktopBanner;
      }

      $mobileBanner = ParagraphHelper::buildImageItem(
        $banner, 
        $this->fileUrlGenerator, 
        'field_mobile_banner', 
        $this->entityRepository
      );
      if (!empty($mobileBanner)) {
        $bannerLists[$banner->id()]['field_mobile_banner'] = $mobileBanner;
      }

      $desktopVideoBanner = ParagraphHelper::getVideoFileUrl(
        $banner,
        $this->fileUrlGenerator,
        'field_desktop_video_banner',
        $this->entityRepository
      );
      if (!empty($desktopVideoBanner)) {
        $bannerLists[$banner->id()]['field_desktop_video_banner'] = $desktopVideoBanner;
      }

      $mobileVideoBanner = ParagraphHelper::getVideoFileUrl(
        $banner,
        $this->fileUrlGenerator,
        'field_mobile_video_banner',
        $this->entityRepository
      );
      if (!empty($mobileVideoBanner)) {
        $bannerLists[$banner->id()]['field_mobile_video_banner'] = $mobileVideoBanner;
      }
    }

    return $bannerLists;
  }

  /**
   * Get all Featured Banner.
   */
  private function getAllBannerFeaturedInFrontPage() {
		// Return an array of node entities that have the
		// `field_featured_on_frontpage_bann` field set to true.
		$storage = $this->entityTypeManager->getStorage('node');

		$query = $storage->getQuery()
			->condition('status', 1)
			->condition('field_featured_on_frontpage_bann', 1)
      ->accessCheck(FALSE)
			->sort('created', 'DESC');

		$nids = $query->execute();
		if (empty($nids)) {
			return [];
		}

		return $storage->loadMultiple($nids);
  }
}
