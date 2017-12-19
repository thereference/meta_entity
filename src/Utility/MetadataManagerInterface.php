<?php

namespace Drupal\meta_entity\Utility;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Class MetadataManager.
 *
 * @package Drupal\metatag
 */
interface MetadataManagerInterface {

  /**
   * Extracts all tags of a given entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to extract metatags from.
   *
   * @return array
   *   Array of metatags.
   */
  public function tagsFromEntity(ContentEntityInterface $entity);

}
