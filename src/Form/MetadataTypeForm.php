<?php

namespace Drupal\meta_entity\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;

class MetadataTypeForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity_type = $this->entity;
    $content_entity_id = $entity_type->getEntityType()->getBundleOf();

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity_type->label(),
      '#description' => $this->t("Label for the %content_entity_id entity type (bundle).", ['%content_entity_id' => $content_entity_id]),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\meta_entity\Entity\MetadataEntityType::load',
      ],
      '#disabled' => !$entity_type->isNew(),
    ];

    $form['description'] = [
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $entity_type->getDescription(),
      '#description' => t('This text will be displayed on the metadata overview page.'),
    ];

    $form['additional_settings'] = [
      '#type' => 'vertical_tabs',
      '#attached' => [
        'library' => ['node/drupal.content_types'],
      ],
    ];

    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => t('Language settings'),
        '#group' => 'additional_settings',
      ];

      $language_configuration = ContentLanguageSettings::loadByEntityTypeBundle('metadata', $entity_type->id());
      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'metadata',
          'bundle' => $entity_type->id(),
        ],
        '#default_value' => $language_configuration,
      ];
    }

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity_type = $this->entity;
    $status = $entity_type->save();
    $message_params = [
      '%label' => $entity_type->label(),
      '%content_entity_id' => $entity_type->getEntityType()->getBundleOf(),
    ];

    // Provide a message for the user and redirect them back to the collection.
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label %content_entity_id entity type.', $message_params));
        break;

      default:
        drupal_set_message($this->t('Saved the %label %content_entity_id entity type.', $message_params));
    }

    $form_state->setRedirectUrl($entity_type->toUrl('collection'));
  }
}