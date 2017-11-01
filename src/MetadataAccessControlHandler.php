<?php
/**
 * Created by PhpStorm.
 * User: s.deboeck
 * Date: 29/08/2017
 * Time: 11:54
 */

namespace Drupal\meta_entity;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the settings entity type.
 *
 * @see \Drupal\meta_entnty\Entity\Metadata
 * @ingroup settings_access
 */
class MetadataAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'access content');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ["edit metadata in {$entity->bundle()}", 'administer settings'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ["delete metadata in {$entity->bundle()}", 'administer settings'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer settings');
  }
}