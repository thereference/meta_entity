<?php

namespace Drupal\meta_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the metadata defaults entity.
 *
 * @ConfigEntityType(
 *   id = "metadata_defaults",
 *   label = @Translation("Metadata defaults"),
 *   config_prefix = "metadata_defaults",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *   }
 * )
 */
class MetadataDefaults extends ConfigEntityBase {

  /**
   * The Metatag defaults ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The default tag values.
   *
   * @var array
   */
  protected $defaults = [];

  /**
   * Returns the value of a default.
   *
   * @param string $meta_id
   *   The identifier of the default.
   *
   * @return array|NULL
   *   array containing the tag values or NULL if not found.
   */
  public function getDefault($meta_id) {
    if (!$this->hasDefault($meta_id)) {
      return NULL;
    }
    return $this->defaults[$meta_id];
  }

  /**
   * Returns TRUE if a default exists.
   *
   * @param string $meta_id
   *   The identifier of the meta.
   *
   * @return boolean
   *   TRUE if the tag exists.
   */
  public function hasDefault($meta_id) {
    return array_key_exists($meta_id, $this->defaults);
  }

  /**
   * Overwrite the current tags with new values.
   *
   * @param array $new_defaults
   */
  public function overwriteDefaults(array $new_defaults = []) {
    if (!empty($new_defaults)) {
      // Get the existing tags.
      $combined_defaults = $this->get('defaults');

      // Loop over the new tags, adding them to the existing tags.
      foreach ($new_defaults as $meta_id => $data) {
        $combined_defaults[$meta_id] = $data;
      }

      // Save the combination of the existing tags + the new tags.
      $this->set('defaults', $combined_defaults);
    }
  }

}
