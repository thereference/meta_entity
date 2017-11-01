<?php

namespace Drupal\meta_entity\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'metatag' entity field type.
 *
 * @FieldType(
 *   id = "metadata",
 *   label = @Translation("Metadata"),
 *   description = @Translation("An entity field containing metadata"),
 *   no_ui = TRUE,
 *   default_widget = "metadata"
 * )
 */
class MetadataItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['title'] = DataDefinition::create('string')
        ->setLabel(t('Metatag title'))
        ->setComputed(TRUE);
    $properties['id'] = DataDefinition::create('integer')
        ->setLabel(t('Metatag id'))
        ->setComputed(TRUE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array();
  }

    /**
     * {@inheritdoc}
     */
    public function preSave() {
        $this->metatag = trim($this->metatag);
    }

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {
//      $entity = $this->getEntity();
//      $metatagclass = new \stdClass();
//      $metatagclass->title = $this->title;
//      $metatagclass->description = $this->description;
//      $metatagclass->image = $this->image;
//
//      if(!$update && $this->title && $this->description && $this->image) {
//          \Drupal::service('did_metatag.metatag_storage')->delete(array('source' => $this->source));
//          $metatag = \Drupal::service('did_metatag.metatag_storage')->save($metatagclass, '/' . $entity->urlInfo()->getInternalPath(), $this->getLangcode());
//          if ($metatag) {
//         $this->id = $metatag['id'];
//        }
//    }
//    else {
//      // Delete old metatag if user erased it.
//      if ($this->source && !$this->title && !$this->description) {
//       \Drupal::service('did_metatag.metatag_storage')->delete(array('source' => $this->source));
//      }
//      // Only save a non-empty metatag.
//      elseif ($this->title && $this->description) {
//      \Drupal::service('did_metatag.metatag_storage')->save($metatagclass, '/' . $entity->urlInfo()->getInternalPath(), $this->getLangcode());
//      }
//    }
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'metatag';
  }

}
