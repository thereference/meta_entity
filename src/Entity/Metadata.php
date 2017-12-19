<?php

namespace Drupal\meta_entity\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the settings entity.
 *
 * @ContentEntityType(
 *   id = "metadata",
 *   label = @Translation("Metadata"),
 *   base_table = "metadata",
 *   data_table = "metadata_field_data",
 *   entity_keys = {
 *     "id" = "id",
 *     "langcode" = "langcode",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "title" = "title",
 *     "description" = "description",
 *   },
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\meta_entity\Form\MetadataForm",
 *       "edit" = "Drupal\meta_entity\Form\MetadataForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "storage_schema" =
 *   "Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema",
 *     "access" = "Drupal\meta_entity\MetadataAccessControlHandler",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   links = {
 *   },
 *   permission_granularity = "bundle",
 *   bundle_entity_type = "metadata_type",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   field_ui_base_route = "entity.metadata_type.edit_form",
 * )
 */
class Metadata extends ContentEntityBase implements MetadataInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type
  ) {
    $fields = parent::baseFieldDefinitions($entity_type);

    if ($entity_type->hasKey('title')) {
      $fields[$entity_type->getKey('title')] = BaseFieldDefinition::create(
        'string'
      )
        ->setLabel(new TranslatableMarkup('Title'))
        ->setRequired(TRUE)
        ->setTranslatable(TRUE)
        ->setDisplayConfigurable('form', TRUE);
    }

    if ($entity_type->hasKey('description')) {
      $fields[$entity_type->getKey(
        'description'
      )] = BaseFieldDefinition::create('string_long')
        ->setLabel(new TranslatableMarkup('Description'))
        ->setRequired(TRUE)
        ->setTranslatable(TRUE)
        ->setDisplayConfigurable('form', TRUE);
    }

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(new TranslatableMarkup('Image'))
      ->setRequired(FALSE)
      ->setTranslatable(TRUE)
      ->setSettings(
        [
          'alt_field' => 0,
          'alt_field_required' => 0,
          'title_field' => 0,
          'title_field_required' => 0,
        ]
      )
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

  /**
   * Get the title that is filled in for this entity.
   */
  public function getTitle() {
    return $this->get('title')->getString();
  }

  /**
   * Get the description that is filled in for this entity.
   */
  public function getDescription() {
    return $this->get('description')->getString();
  }

  /**
   * Get the file that is uploaded in for this entity.
   */
  public function getImage() {

    if ($this->get('image')->isEmpty()) {
      return FALSE;
    }

    return $this->get('image')->entity;
  }
}