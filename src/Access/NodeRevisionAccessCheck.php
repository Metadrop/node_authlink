<?php

namespace Drupal\node_authlink\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Access\NodeRevisionAccessCheck as NodeRevisionAccessCheckOriginal;
use Drupal\node\NodeInterface;

/**
 * Class NodeRevisionAccessCheck
 *
 * @package Drupal\node_authlink\Access
 */
class NodeRevisionAccessCheck extends NodeRevisionAccessCheckOriginal {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(NodeInterface $node, AccountInterface $account, $op = 'view') {
    if (node_authlink_node_is_enabled($node) && node_authlink_check_authlink($node, $op, $account)) {
      return AccessResult::allowed();
    }

    return parent::checkAccess($node, $account, $op);
  }

}
