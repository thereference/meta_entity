<?php

namespace Drupal\meta_entity\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormBase;
use Drupal\meta_entity\Entity\Metadata;

/**
 * Plugin implementation of the 'metadata' widget.
 *
 * @FieldWidget(
 *   id = "metadata",
 *   label = @Translation("Metadata"),
 *   field_types = {
 *     "metadata"
 *   }
 * )
 */
class MetadataWidget extends InlineEntityFormBase
{

    /**
     * {@inheritdoc}
     */
    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
        $parent = $items->getEntity();







      // Trick inline_entity_form_form_alter() into attaching the handlers,
      // WidgetSubmit will be needed once extractFormValues fills the $form_state.
      $parents = array_merge($element['#field_parents'], [$items->getName()]);
      $ief_id = sha1(implode('-', $parents));
      $form_state->set(['inline_entity_form', $ief_id], []);

      $element = [
          '#type' => 'fieldset',
          '#field_title' => $this->t('Metadata'),
          '#after_build' => [
            [get_class($this), 'removeTranslatabilityClue'],
          ],
        ] + $element;

      $item = $items->get($delta);

      if ($item->target_id && !$item->entity) {
        $element['warning']['#markup'] = $this->t('Unable to load the referenced entity.');
        return $element;
      }

      $entity = Metadata::loadByParent($parent);
      $op = $entity ? 'edit' : 'add';

      $langcode = $items->getEntity()->language()->getId();
      $parents = array_merge($element['#field_parents'], [
        $items->getName(),
        $delta,
        'inline_entity_form'
      ]);
      $bundle = 'default';
      $element['inline_entity_form'] = $this->getInlineEntityForm($op, $bundle, $langcode, $delta, $parents, $entity);

      if ($op == 'edit') {
        /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
        if (!$entity->access('update')) {
          // The user isn't allowed to edit the entity, but still needs to see
          // it, to be able to reorder values.
          $element['entity_label'] = [
            '#type' => 'markup',
            '#markup' => $entity->label(),
          ];
          // Hide the inline form. getInlineEntityForm() still needed to be
          // called because otherwise the field re-ordering doesn't work.
          $element['inline_entity_form']['#access'] = FALSE;
        }
      }
      return $element;
















//        $metatag = array();
//        if (!$entity->isNew()) {
//            $conditions = array('source' => '/' . $entity->urlInfo()->getInternalPath());
//            if ($items->getLangcode() != LanguageInterface::LANGCODE_NOT_SPECIFIED) {
//                $conditions['langcode'] = $items->getLangcode();
//            }
//            $metatag = \Drupal::service('did_metatag.metatag_storage')->load($conditions);
//            if ($metatag === FALSE) {
//                $metatag = array();
//            }
//        }

        $element += array(
            '#element_validate' => array(array(get_class($this), 'validateFormElement')),
        );
//
//        $metatag += array(
//            'id' => NULL,
//            'source' => !$entity->isNew() ? '/' . $entity->urlInfo()->getInternalPath() : NULL,
//            'title' => '',
//            'description' => '',
//            'image' => '',
//            'langcode' => $items->getLangcode(),
//        );

        $element['id'] = array(
            '#type' => 'value',
            '#value' => '',//$metatag['id'],
        );

        $element['title'] = array(
            '#type' => 'textfield',
            '#title' => 'Title',
            '#default_value' => '',//$metatag['title'],
            '#required' => FALSE,
            '#maxlength' => 255,
            '#description' => $this->t('Specify a title for the metatag.'),
        );

        $element['description'] = array(
            '#type' => 'textarea',
            '#title' => 'Description',
            '#default_value' => '',//$metatag['description'],
            '#required' => FALSE,
            '#description' => $this->t('Specify a description for the metatag.'),

        );

        $element['image'] = array(
            '#type' => 'managed_file',
            '#title' => 'Image',
            '#default_value' => '',//array($metatag['image']),
            '#required' => FALSE,
            '#description' => $this->t('Specify an image for the metatag.'),
            '#upload_location' => 'public://metatags',
        );

        return $element;
    }

    public static function validateFormElement(array &$element, FormStateInterface $form_state) {

    }


  /**
   * Creates an instance of the inline form handler for the current entity type.
   */
  protected function createInlineFormHandler() {
    if (!isset($this->inlineFormHandler)) {
      $target_type = 'metadata';
      $this->inlineFormHandler = $this->entityTypeManager->getHandler($target_type, 'inline_form');
    }
  }

  /**
   * Gets inline entity form element.
   *
   * @param string $operation
   *   The operation (i.e. 'add' or 'edit').
   * @param string $bundle
   *   Entity bundle.
   * @param string $langcode
   *   Entity langcode.
   * @param array $parents
   *   Array of parent element names.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Optional entity object.
   *
   * @return array
   *   IEF form element structure.
   */
  protected function getInlineEntityForm($operation, $bundle, $langcode, $delta, array $parents, EntityInterface $entity = NULL) {
    $element = [
      '#type' => 'inline_entity_form',
      '#entity_type' => 'metadata',
      '#bundle' => $bundle,
      '#langcode' => $langcode,
      '#default_value' => $entity,
      '#op' => $operation,
      '#form_mode' => 'default',
      '#save_entity' => FALSE,
      '#ief_row_delta' => $delta,
      // Used by Field API and controller methods to find the relevant
      // values in $form_state.
      '#parents' => $parents,
      // Labels could be overridden in field widget settings. We won't have
      // access to those in static callbacks (#process, ...) so let's add
      // them here.
      '#ief_labels' => $this->getEntityTypeLabels(),
      // Identifies the IEF widget to which the form belongs.
      '#ief_id' => $this->getIefId(),
    ];

    return $element;
  }

  /**
   * Gets the entity type managed by this handler.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   The entity type.
   */
  protected function getEntityTypeLabels() {
    $this->createInlineFormHandler();
    return $this->inlineFormHandler->getEntityTypeLabels();
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    if ($this->isDefaultValueWidget($form_state)) {
      $items->filterEmptyItems();
      return;
    }

    $field_name = $this->fieldDefinition->getName();
    $parents = array_merge($form['#parents'], [$field_name]);
    $submitted_values = $form_state->getValue($parents);
    $values = [];
    foreach ($items as $delta => $value) {
      $element = NestedArray::getValue($form, [$field_name, 'widget', $delta]);
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $element['inline_entity_form']['#entity'];
      $weight = isset($submitted_values[$delta]['_weight']) ? $submitted_values[$delta]['_weight'] : 0;
      $values[$weight] = ['entity' => $entity];
    }

    // Sort items base on weights.
    ksort($values);
    $values = array_values($values);

    // Let the widget massage the submitted values.
    $values = $this->massageFormValues($values, $form, $form_state);

    // Assign the values and remove the empty ones.
    $items->setValue($values);
    $items->filterEmptyItems();

    // Populate the IEF form state with $items so that WidgetSubmit can
    // perform the necessary saves.
    $ief_id = sha1(implode('-', $parents));
    $widget_state = [
      'instance' => $this->fieldDefinition,
      'delete' => [],
      'entities' => [],
    ];
    foreach ($items as $delta => $value) {
      TranslationHelper::updateEntityLangcode($value->entity, $form_state);
      $widget_state['entities'][$delta] = [
        'entity' => $value->entity,
        'needs_save' => TRUE,
      ];
    }
    $form_state->set(['inline_entity_form', $ief_id], $widget_state);

    // Put delta mapping in $form_state, so that flagErrors() can use it.
    $field_name = $this->fieldDefinition->getName();
    $field_state = WidgetBase::getWidgetState($form['#parents'], $field_name, $form_state);
    foreach ($items as $delta => $item) {
      $field_state['original_deltas'][$delta] = isset($item->_original_delta) ? $item->_original_delta : $delta;
      unset($item->_original_delta, $item->weight);
    }
    WidgetBase::setWidgetState($form['#parents'], $field_name, $form_state, $field_state);
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return TRUE;
    $handler_settings = $field_definition->getSettings()['handler_settings'];
    $target_entity_type = \Drupal::entityTypeManager()->getDefinition('metadata');
    // The target entity type doesn't use bundles, no need to validate them.
    if (!$target_entity_type->getKey('bundle')) {
      return TRUE;
    }

    if (empty($handler_settings['target_bundles'])) {
      return FALSE;
    }

    if (count($handler_settings['target_bundles']) != 1) {
      return FALSE;
    }

    return TRUE;
  }
}
