<?php

namespace Drupal\meta_entity\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\Entity;
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
 *     "image" = "image",
 *     "parent" = "parent",
 *   },
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\meta_entity\Form\MetadataForm",
 *       "edit" = "Drupal\meta_entity\Form\MetadataForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "storage_schema" = "Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema",
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
//
//*     "edit-form" = "/admin/structure/settings_type/settings/{settings}/edit",
// *     "collection" = "/admin/structure/settings_type/settings",
// *     "canonical" = "/admin/structure/settings_type/settings/{settings}",
class Metadata extends ContentEntityBase implements MetadataInterface {

  /**
   * {@inheritdoc}
   */
  public function getParent() {

  }

  /**
   * {@inheritdoc}
   */
  public function setParent(Entity $entity) {

  }

  /**
   * {@inheritdoc}
   */
  public static function loadByParent(Entity $entity) {

  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    if ($entity_type->hasKey('title')) {
      $fields[$entity_type->getKey('title')] = BaseFieldDefinition::create('string')
        ->setLabel(new TranslatableMarkup('Title'))
        ->setTranslatable(TRUE)
        ->setDisplayConfigurable('form', TRUE);
    }

    if ($entity_type->hasKey('description')) {
      $fields[$entity_type->getKey('description')] = BaseFieldDefinition::create('text_long')
        ->setLabel(new TranslatableMarkup('Description'))
        ->setTranslatable(TRUE)
        ->setDisplayConfigurable('form', TRUE);
    }

    if ($entity_type->hasKey('image')) {
      $fields[$entity_type->getKey('image')] = BaseFieldDefinition::create('image')
        ->setLabel(new TranslatableMarkup('Image'))
        ->setTranslatable(TRUE)
        ->setDisplayConfigurable('form', TRUE);
    }

    if ($entity_type->hasKey('parent')) {
      $fields[$entity_type->getKey('parent')] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(new TranslatableMarkup('Parent'))
        ->setRequired(TRUE);
    }

    return $fields;
  }
}