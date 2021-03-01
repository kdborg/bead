<?php

namespace Drupal\bead\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\bead\Scan;

class BeadProjectScanForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'bead_scan_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['actions']['#type'] = 'actions';

    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = 0;
    if ($node instanceof \Drupal\node\NodeInterface) {
      // You can get nid and anything else you need from the node object.
      $nid = $node->id();
    }

    $form['actions']['project_id'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Scan'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $action = $form_state->getTriggeringElement()['#value'];

    $nid = $form_state->getValue('project_id');

    if ($action == 'Scan') {
      $scan = new Scan();
      $scan->scan($nid);
    }
  }
}
