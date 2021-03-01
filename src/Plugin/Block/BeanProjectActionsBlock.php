<?php

namespace Drupal\bead\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormInterface;

/**
 * Class BeanProjectActionsBlock
 *
 * @package Drupal\bead\Plugin\Block
 *
 * @Block(
 *   id = "bean_project_actions_block",
 *   admin_label = @Translation("Project Actions Block"),
 * )
 */
class BeanProjectActionsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\bead\Form\BeadProjectScanForm');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }
  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['bead_project_actions_block'] = $form_state->getValue('bead_project_actions_block');
  }

}
