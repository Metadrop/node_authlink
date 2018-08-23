<?php

namespace Drupal\node_authlink\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class NodeAuthlinkNodeForm.
 */
class NodeAuthlinkNodeForm extends FormBase {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * Constructs a new NodeAuthlinkNodeForm object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory
  ) {
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_authlink_node_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    if (!is_numeric($node)) {
      throw new NotFoundHttpException();
    }

    $config = $this->configFactory->get('node_authlink.settings');
    $config_grants = $config->get('grants');

    $node = Node::load($node);

    if (isset($config_grants[$node->bundle()])) {
      foreach ($config_grants[$node->bundle()] as $op) {
        if (!$op) {
          continue;
        }
        $url = node_authlink_get_url($node, $op);
        if ($url) {

          $form['link_'.$op] = [
            '#type' => 'markup',
            '#markup' => '<p>' . $url . '</p>',
          ];
          $form['delete_' . $op] = [
            '#type' => 'submit',
            '#value' => $this->t('Delete @op authlink', ['@op' => $op]),
            '#weight' => '0',
            '#submit' => ['::deleteAuthlink' . ucfirst($op)]
          ];
        }
        else {
          $form['create_' . $op] = [
            '#type' => 'submit',
            '#value' => $this->t('Create @op authlink', ['@op' => $op]),
            '#weight' => '0',
            '#submit' => ['::createAuthlink' . ucfirst($op)]
          ];
        }

      }
    }


    return $form;
  }

  public function createAuthlink(array &$form, FormStateInterface $form_state, $op) {

  }

  public function deleteAuthlink(array &$form, FormStateInterface $form_state, $op) {

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

  }

  /**
   * Checks that node_authlink was enabled for this content type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param $node
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   */
  public function access(AccountInterface $account, $node) {
    if (is_numeric($node)) {
      $node = Node::load($node);
      $enable = $this->config('node_authlink.settings')->get('enable');
      if (isset($enable[$node->bundle()]) && $enable[$node->bundle()]) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden();
  }
}
