<?php
/**
 * Created by PhpStorm.
 * User: s.deboeck
 * Date: 19/12/2017
 * Time: 11:34
 */

namespace Drupal\meta_entity\Utility;


use Drupal\meta_entity\Entity\MetadataDefaults;

trait SupportTrait {
  protected static $allowedEntityTypes = [
    'node_type' => 'Node type',
    'taxonomy_vocabulary' => 'Taxonomy vocabulary',
  ];

  public static $mappedInstances = [
    'node_type' => 'node',
    'taxonomy_vocabulary' => 'taxonomy_term',
  ];

  /**
   * @var string|null
   */
  protected $entityCategory;

  /**
   * @var string
   */
  protected $entityTypeId;

  /**
   * @var string
   */
  protected $bundleName;

  /**
   * Returns the metadata defaults entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|static
   */
  protected function loadDefaults() {
    $config_key = $this->getConfigKey();
    $default = MetadataDefaults::load($config_key);
    if (!$default) {
      $default = MetadataDefaults::create(['id' => $config_key]);
    }

    return $default;
  }

  /**
   * Returns the config key of this object.
   *
   * @return string
   */
  public function getConfigKey() {
    $key = [];
    $key[] = $this->getEntityCategory();
    $key[] = $this->getEntityTypeId();
    $key[] = $this->getBundleName();

    return implode('.', $key);
  }

  /**
   * @return null|string
   */
  public function getEntityCategory() {
    return $this->entityCategory;
  }

  /**
   * @param string $entity_category
   *
   * @return $this
   */
  public function setEntityCategory($entity_category) {
    $this->entityCategory = $entity_category;
    return $this;
  }


  /**
   * @return string
   */
  public function getEntityTypeId() {
    return $this->entityTypeId;
  }

  /**
   * @param string $entity_type_id
   *
   * @return $this
   */
  public function setEntityTypeId($entity_type_id) {
    $this->entityTypeId = $entity_type_id;
    return $this;
  }

  /**
   * @return string
   */
  public function getBundleName() {
    return $this->bundleName;
  }

  /**
   * @param string $bundle_name
   *
   * @return $this
   */
  public function setBundleName($bundle_name) {
    $this->bundleName = $bundle_name;
    return $this;
  }
}