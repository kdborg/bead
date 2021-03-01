<?php

namespace Drupal\bead\Plugin\views\query;

use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ProjectType
 *
 * @package Drupal\bead\Plugin\views\query
 *
 * @ViewsQuery(
 *   id = "project_types",
 *   title = @Translation("Project Type"),
 *   help = @Translation("Query against project types.")
 * )
 */
class ProjectType extends QueryPluginBase {

  /**
   * ProjectType constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   *
   * @return \Drupal\Core\Plugin\ContainerFactoryPluginInterface|\Drupal\views\Plugin\views\query\QueryPluginBase
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return parent::create($container, $configuration, $plugin_id, $plugin_definition);
  }

  public function ensureTable($table, $relationship = NULL) {
    return '';
  }

  public function addField($table, $field, $alias = '', $params = []) {
    return $field;
  }

  public function execute(ViewExecutable $view) {
    $index = 0;
    $types = bead_project_type_get_types();

    foreach ($types as $project_type_id => $name) {
      $view->result[] = new ResultRow([
        'project_type_id' => $project_type_id,
        'name' => $name,
        'index' => $index++,
      ]);
    }
  }

  public function addOrderBy() {

  }
}
