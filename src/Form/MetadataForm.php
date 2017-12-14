<?php
/**
 * Created by PhpStorm.
 * User: s.deboeck
 * Date: 31/08/2017
 * Time: 11:32
 */

namespace Drupal\meta_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm
 *
 * @package Drupal\meta_entity\Form
 */
class MetadataForm extends ContentEntityForm {
  
  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $settings = $this->entity;
    $settings->save();

    $insert = $settings->isNew();

    if (!$insert) {

      $context = [
        '@type' => $settings->getEntityType()->getLabel(),
        '%title' => $settings->label(),
        'link' => $settings->toLink('Edit', 'edit-form')
          ->toString()
          ->getGeneratedLink(),
      ];
      $t_args = [
        '@type' => $settings->getEntityType()->getLabel(),
        '%title' => $settings->toLink(NULL, 'edit-form')->toString(),
      ];

      $this->logger('content')->notice('@type: updated %title.', $context);
      drupal_set_message(t('@type %title has been updated.', $t_args));
    }

    if ($settings->id()) {
      $form_state->setRedirect('entity.settings_type.collection');
    }
    else {
      // In the unlikely case something went wrong on save, the settings will be
      // rebuilt and settings form redisplayed the same way as in preview.
      drupal_set_message(t('The post could not be saved.'), 'error');
      $form_state->setRebuild();
    }
  }
}