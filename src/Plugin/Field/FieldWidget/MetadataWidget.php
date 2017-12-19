<?php

namespace Drupal\meta_entity\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormSimple;
use Drupal\meta_entity\Entity\MetadataDefaults;

/**
 * Metadata inline entity widget.
 *
 * @FieldWidget(
 *   id = "metadata_inline_entity",
 *   label = @Translation("Inline entity form - Metadata"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = FALSE
 * )
 */
class MetadataWidget extends InlineEntityFormSimple {

  protected static $mappedInstances = [
    'node' => 'node_type',
    'taxonomy_term' => 'taxonomy_vocabulary',
  ];

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $new_element = [];
    $parent = $items->getEntity();
    $entity_type = $parent->getEntityTypeId();

    $new_element += \Drupal::service('meta_entity.token')->tokenBrowser([$entity_type]);

    $new_element += parent::formElement($items, $delta, $element, $form, $form_state);

    // Override parent settings
    $new_element['#group'] = 'advanced';
    $new_element['#type'] = 'details';

    // Add default for our entity if there isn't an entity yet.
    $entity_form = $new_element['inline_entity_form'];
    if (!$entity_form['#default_value'] && isset(self::$mappedInstances[$parent->getEntityTypeId()])) {
      // This is an add operation, create a new entity.
      $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_form['#entity_type']);
      $storage = \Drupal::entityTypeManager()->getStorage($entity_form['#entity_type']);
      $values = [];
      if ($langcode_key = $entity_type->getKey('langcode')) {
        if (!empty($entity_form['#langcode'])) {
          $values[$langcode_key] = $entity_form['#langcode'];
        }
      }
      if ($bundle_key = $entity_type->getKey('bundle')) {
        $values[$bundle_key] = $entity_form['#bundle'];
      }

      $config_key = 'instance.' . self::$mappedInstances[$parent->getEntityTypeId()] . '.' . $parent->bundle();
      $defaults = MetadataDefaults::load($config_key);

      $values['title'] = $defaults->getDefault('title');
      $values['description'] = $defaults->getDefault('description');

      // Convert the stored UUID to a FID.
      $uuid = $defaults->getDefault('image');

      if ($uuid && ($file = \Drupal::entityManager()->loadEntityByUuid('file', $uuid))) {
        $values['image'] = $file;
      }

      $new_element['inline_entity_form']['#default_value'] = $storage->create($values);
    }

    return $new_element;
  }

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $element = parent::formMultipleElements($items, $form, $form_state);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if (!parent::isApplicable($field_definition)) {
      return FALSE;
    }
    // @todo check if it is a metadata entity.

    return TRUE;
  }

}
