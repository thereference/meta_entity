<?php

namespace Drupal\meta_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Settings Type
 *
 * @ConfigEntityType(
 *   id = "metadata_type",
 *   label = @Translation("Metadata Type"),
 *   bundle_of = "metadata",
 *   config_prefix = "metadata_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_prefix = "metadata_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   },
 *   handlers = {
 *     "list_builder" =
 *   "Drupal\meta_entity\Controller\MetadataTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\meta_entity\Form\MetadataTypeForm",
 *       "add" = "Drupal\meta_entity\Form\MetadataTypeForm",
 *       "edit" = "Drupal\meta_entity\Form\MetadataTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer metadata",
 *   links = {
 *     "canonical" = "/admin/config/search/metadata/{metadata_type}",
 *     "add-form" = "/admin/config/search/metadata/add",
 *     "edit-form" = "/admin/config/search/metadata/{metadata_type}/edit",
 *     "delete-form" = "/admin/config/search/metadata/{metadata_type}/delete",
 *     "collection" = "/admin/config/search/metadata",
 *   }
 * )
 */
class MetadataType extends ConfigEntityBundleBase {

  /**
   * The machine name of this metadata type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the metadata type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this metadata type.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }
}