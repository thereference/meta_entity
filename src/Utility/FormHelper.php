<?php

namespace Drupal\meta_entity\Utility;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\meta_entity\Entity\MetadataDefaults;
use Drupal\meta_entity\Entity\MetadataType;

/**
 * Token handling service. Uses core token service or contributed Token.
 */
class FormHelper {
  use SupportTrait;

  protected static $allowedFormOperations = [
    'default',
    'edit',
    'add',
    'register',
  ];

  /**
   * @var \Drupal\Core\Form\FormState
   */
  protected $formState;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * FormHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $fieldManager
   */
  public function __construct(
    EntityTypeManager $entityTypeManager,
    AccountProxyInterface $current_user,
    EntityFieldManagerInterface $fieldManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $current_user;
    $this->entityFieldManager = $fieldManager;
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return bool
   */
  public function processForm(FormStateInterface $form_state) {
    $this->formState = $form_state;
    $this->cleanUpFormInfo();
    $this->getEntityDataFromFormEntity();

    return $this->supports();
  }

  /**
   * Removes gathered form information from service object.
   *
   * Needed because this service may contain form info from the previous
   * operation when revived from the container.
   */
  protected function cleanUpFormInfo() {
    $this->entityCategory = NULL;
    $this->entityTypeId = NULL;
    $this->bundleName = NULL;
    $this->instanceId = NULL;
  }

  /**
   * Gets the object entity of the form if available.
   *
   * @return \Drupal\Core\Entity\Entity|false
   *   Entity or FALSE if non-existent or if form operation is
   *   'delete'.
   */
  protected function getFormEntity() {
    $form_object = $this->formState->getFormObject();
    if (NULL !== $form_object
      && method_exists($form_object, 'getOperation')
      && method_exists($form_object, 'getEntity')
      && in_array($form_object->getOperation(), self::$allowedFormOperations)) {
      return $form_object->getEntity();
    }
    return FALSE;
  }


  /**
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return bool
   */
  public function processFormSubmit(FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if (!isset($values['metadata'])) {
      return FALSE;
    }
    $new_values = $values['metadata'];

    $this->formState = $form_state;
    $this->cleanUpFormInfo();
    $this->getEntityDataFromFormEntity();

    $defaults = $this->loadDefaults();
    $defaults->overwriteDefaults($new_values);
    $defaults->save();
  }

  /**
   * Add the metadata settings to a form element.
   *
   * @param array $form
   */
  public function displayMetadataSettings(&$form = []) {
    $options = [
      '_none' => new TranslatableMarkup('Disabled'),
    ];

    $metadataTypes = MetadataType::loadMultiple();
    foreach ($metadataTypes as $metadataType) {
      $options[$metadataType->id()] = $metadataType->label();
    }

    $defaults = $this->loadDefaults();

    $form['enabled'] = [
      '#type' => 'radios',
      '#title' => t('Enabled metadata'),
      '#default_value' => $defaults->getDefault('enabled'),
      '#options' => $options,
      '#description' => t('The enabled metadata type for this entity type'),
    ];

    // @todo Create the default values from field definitions. Maybe with a ajax field reload, so it adds the right fields for each meta type
    $token_type = isset(
      self::$mappedInstances[$this->getEntityTypeId()]
    ) ? self::$mappedInstances[$this->getEntityTypeId()] : [];
    $form += \Drupal::service('meta_entity.token')->tokenBrowser([$token_type]);

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => new TranslatableMarkup('Title'),
      '#default_value' => $defaults->getDefault('title'),
      '#description' => t('The default value for the metadata title'),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => new TranslatableMarkup('Description'),
      '#default_value' => $defaults->getDefault('description'),
      '#description' => t('The default value for the metadata description'),
    ];

    // Convert the stored UUID to a FID.
    $fids = [];
    $uuid = $defaults->getDefault('image');

    if ($uuid && ($file = $this->getEntityManager()->loadEntityByUuid(
        'file',
        $uuid
      ))) {
      $fids[0] = $file->id();
    }

    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => new TranslatableMarkup('Image'),
      '#default_value' => $fids,
      '#description' => t('Image to be shown if no image is uploaded.'),
      '#upload_location' => 'public://default_images/',
      '#element_validate' => [
        '\Drupal\file\Element\ManagedFile::validateManagedFile',
        '\Drupal\image\Plugin\Field\FieldType\ImageItem::validateDefaultImageForm',
      ],
    ];

  }

  /**
   * Gets the entity manager.
   *
   * @return \Drupal\Core\Entity\EntityManagerInterface
   */
  protected function getEntityManager() {
    if (!isset($this->entityManager)) {
      $this->entityManager = \Drupal::entityManager();
    }
    return $this->entityManager;
  }

  /**
   * @return bool
   */
  protected function supports() {

    // Do not alter the form if user lacks certain permissions.
    if (!$this->currentUser->hasPermission('administer metadata')) {
      return FALSE;
    }

    // Do not alter the form if it is irrelevant to sitemap generation.
    elseif (empty($this->getEntityCategory())) {
      return FALSE;
    }

    if (!isset(self::$allowedEntityTypes[$this->getEntityTypeId()])) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Checks if this particular form is a bundle form, or a bundle instance form
   * and gathers sitemap settings from the database.
   *
   * @return bool
   *   TRUE if this is a bundle or bundle instance form, FALSE otherwise.
   */
  protected function getEntityDataFromFormEntity() {
    $form_entity = $this->getFormEntity();

    if ($form_entity !== FALSE) {
      $entity_type_id = $form_entity->getEntityTypeId();
      $entity_types = self::$allowedEntityTypes;
      if (isset($entity_types[$entity_type_id])) {
        $this->setEntityCategory('instance');
      }

      switch ($this->getEntityCategory()) {
        case 'instance':
          $this->setEntityTypeId($entity_type_id);
          $this->setBundleName($form_entity->id());
          break;

        default:
          return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }

}
