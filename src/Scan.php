<?php


namespace Drupal\bead;

use Drupal\bead;

class Scan {

  protected $project_id = 0;
  private $project_type = 0;
  protected $base_dir = '';

  /**
   * @var \Drupal\bead\ProjectBase
   */
  private $analyze = NULL;

  public function scan($project_id = 0) {
    if ($project_id <= 0) { return; }

    $this->project_id = $project_id;

    $project_types = bead_project_type_get_types();

    $project = bead_project_get($project_id);

    if (empty($project)) {
      print 'No project found' . PHP_EOL;
      return;
    }

    $directory = $project['directory'];
    $this->project_type = $project['project_type_id'];

    $this->base_dir = $directory;

    switch ($project_types[$this->project_type]) {
      case 'Drupal 7':
        $this->analyze = new Drupal7Project();
        break;
      default:
//        $this->analyze = new PHPProject();
        break;
    }

    if ($this->analyze === NULL) { return; }

    $start_time = time();
    print 'base_dir: ' . $directory . PHP_EOL;
    $this->analyze->scan($project_id, $directory);
    $end_time = time();

    print 'start: ' . $start_time . PHP_EOL;
    print '  end: ' . $end_time . PHP_EOL;
    print 'total: ' . ($end_time - $start_time) . PHP_EOL;
  }
}
