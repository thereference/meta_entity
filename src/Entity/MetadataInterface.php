<?php
namespace Drupal\meta_entity\Entity;


use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity;

interface MetadataInterface extends ContentEntityInterface {

  /**
   * Get the parent.
   *
   * @return \Drupal\Core\Entity\Entity $entity
   *   The parent entity.
   */
  public function getParent();

  /**
   * Sets the parent for the metadata
   *
   * @param \Drupal\Core\Entity\Entity $entity
   *
   * @return $this
   *
   */
  public function setParent(Entity $entity);

  /**
   * Load the metadata by parent entity.
   *
   * @param \Drupal\Core\Entity\Entity $entity
   *
   * @return $this
   *
   */
  public static function loadByParent(Entity $entity);
}