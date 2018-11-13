<?php

namespace Drupal\meta_entity\Utility;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\meta_entity\Entity\MetadataDefaults;
use Drupal\meta_entity\Entity\MetadataInterface;

/**
 * Class MetadataManager.
 *
 * @package Drupal\meta_entity
 */
class MetadataManager implements MetadataManagerInterface {

  use SupportTrait;

  protected static $derivativeData = [
    'title' => [
      'title' => [
        'property' => 'name',
        'key' => 'name',
      ],
      'og:title' => [
        'property' => 'property',
        'key' => 'og_title',
      ],
      'twitter:title' => [
        'property' => 'name',
        'key' => 'twitter_cards_title',
      ],
    ],
    'description' => [
      'description' => [
        'property' => 'name',
        'key' => 'name',
      ],
      'og:description' => [
        'property' => 'property',
        'key' => 'og_description',
      ],
      'twitter:description' => [
        'property' => 'name',
        'key' => 'twitter_cards_description',
      ],
    ],
    'image' => [
      'og:image' => [
        'property' => 'property',
        'key' => 'og_image',
      ],
      'twitter:image' => [
        'property' => 'name',
        'key' => 'twitter_cards_image',
      ],
    ],

    'taxonomy_vocabulary' => 'Taxonomy vocabulary',
  ];

  protected $tokenService;

  /**
   * Metatag logging channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructor for MetatagManager.
   *
   * @param \Drupal\meta_entity\Utility\MetadataToken|\Drupal\meta_entity\Utility\MetatagToken $token
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $channelFactory
   */
  public function __construct(
    MetadataToken $token,
    LoggerChannelFactoryInterface $channelFactory
  ) {
    $this->tokenService = $token;
    $this->logger = $channelFactory->get('meta_entity');
  }

  /**
   * {@inheritdoc}
   */
  public function tagsFromEntity(ContentEntityInterface $entity) {
    $this->getEntityDataFromEntity($entity);
    $metadata_defaults = $this->loadDefaults();

    $enabled = $metadata_defaults->getDefault('enabled');
    if (empty($enabled) || $enabled == '_none') {
      return [];
    }

    $tags = $this->defaultMetadataTags($metadata_defaults);

    // Get the Metadata entity form the content entity.
    $metadata = $this->getMetadataEntity($entity);
    if (!$metadata) {
      return $tags;
    }

    foreach ($this->getMetadataEntityTags($metadata) as $key => $tag) {
      $tags[$key] = $tag;
    }

    return $tags;
  }

  /**
   * Get all the needed entity data.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *
   * @return bool TRUE if we have all the needed data.
   */
  protected function getEntityDataFromEntity(ContentEntityInterface $entity) {
    $mapped_instances = self::$mappedInstances;
    $entity_type = $entity->getEntityTypeId();

    $entity_type_instance = FALSE;
    foreach ($mapped_instances as $instance => $bundle) {
      if ($bundle == $entity_type) {
        $entity_type_instance = $instance;
        break;
      }
    }

    if (isset(self::$allowedEntityTypes[$entity_type_instance])) {
      $this->setEntityCategory('instance');
    }

    switch ($this->getEntityCategory()) {
      case 'instance':
        $this->setEntityTypeId($entity_type_instance);
        $this->setBundleName($entity->bundle());
        break;

      default:
        return FALSE;
    }
    return TRUE;

  }

  /**
   * Get the default metadata from the metadata defaults.
   *
   * @param \Drupal\meta_entity\Entity\MetadataDefaults $defaults
   *
   * @return array
   */
  private function defaultMetadataTags(MetadataDefaults $defaults) {
    $tags = [];

    $tags['title'] = [
      'value' => $defaults->getDefault('title'),
      'token_replaceable' => TRUE,
    ];
    $tags['description'] = [
      'value' => $defaults->getDefault('description'),
      'token_replaceable' => TRUE,
    ];

    $uuid = $defaults->getDefault('image');
    /** @var \Drupal\file\Entity\File $file */
    $file = \Drupal::entityManager()->loadEntityByUuid('file', $uuid);
    if ($file) {
      $tags['image'] =
        [
          'value' => ImageStyle::load('metadata_image')->buildUrl(
            $file->getFileUri()
          ),
          'token_replaceable' => FALSE,
        ];
    }

    return $tags;
  }

  /**
   * Get the metadata entity form the parent entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *
   * @return bool|\Drupal\meta_entity\Entity\MetadataInterface
   */
  private function getMetadataEntity(ContentEntityInterface $entity) {
    if (!$entity->hasField('metadata')) {
      return FALSE;
    }
    $field = $entity->get('metadata');
    $referenced_entities = $field->referencedEntities();
    return !empty($referenced_entities) ? array_pop(
      $referenced_entities
    ) : FALSE;
  }

  /**
   * Get the default metadata from the metadata defaults.
   *
   * @param \Drupal\meta_entity\Entity\MetadataInterface $metadata
   *
   * @return array
   */
  private function getMetadataEntityTags(MetadataInterface $metadata) {
    $tags = [];
    foreach ($metadata->getFields() as $key => $value) {
      $tags[$key] = $metadata->get($key)->getString();
    }

    $title = $metadata->getTitle();
    if ($title) {
      $tags['title'] = [
        'value' => $title,
        'token_replaceable' => TRUE,
      ];
    }

    $description = $metadata->getDescription();
    if ($description) {
      $tags['description'] = [
        'value' => $description,
        'token_replaceable' => TRUE,
      ];
    }

    $file = $metadata->getImage();
    if ($file) {
      $tags['image'] =
        [
          'value' => ImageStyle::load('metadata_image')->buildUrl(
            $file->getFileUri()
          ),
          'token_replaceable' => FALSE,
        ];
    }

    return $tags;
  }

  /**
   * Generate the elements that go in the attached array in
   * hook_page_attachments.
   *
   * @param array $tags
   *   The array of tags as plugin_id => value.
   * @param object $entity
   *   Optional entity object to use for token replacements.
   *
   * @return array
   *   Render array with tag elements.
   */
  public function generateElements($tags, $entity = NULL) {
    $elements = [];

    // Render any tokens in the value.
    $token_replacements = [];
    if ($entity) {
      $token_replacements = [$entity->getEntityTypeId() => $entity];
    }

    $langcode = \Drupal::languageManager()->getCurrentLanguage(
      LanguageInterface::TYPE_CONTENT
    )->getId();


    // Each element of the $values array is a tag type add all the variants for this type
    foreach ($tags as $tag_name => $data) {

      $processed_value = $data['value'];
      if ($data['token_replaceable']) {
        $token_processed = $this->tokenService->replace(
          $data['value'],
          $token_replacements,
          ['langcode' => $langcode]
        );

        $processed_value = PlainTextOutput::renderFromHtml(
          htmlspecialchars_decode($token_processed)
        );
      }

      $type_elements = $this->makeDerivativeElements(
        $tag_name,
        $processed_value
      );
      $elements = array_merge($elements, $type_elements);
    }

    return $elements;
  }

  private function makeDerivativeElements($name, $value) {
    $derivative_info = isset(self::$derivativeData[$name]) ? self::$derivativeData[$name] : FALSE;
    if (!$derivative_info) {
      return [];
    }
    $elements = [];
    foreach ($derivative_info as $key => $item) {
      $elements[] = [
        [
          '#tag' => 'meta',
          '#attributes' => [
            $item['property'] => $key,
            'content' => $value,
          ],
        ],
        $item['key'],
      ];
    }
    return $elements;
  }

}
