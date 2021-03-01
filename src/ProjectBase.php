<?php


namespace Drupal\bead;

use ast;

abstract class ProjectBase {

  protected $base_dir = '';
  protected $project_id = 0;

  /**
   * Start scanning a project.
   *
   * @param int $project_id
   * @param string $base_dir
   */
  public function scan(int $project_id, string $base_dir) {
    $this->project_id = $project_id;
    $this->base_dir = $base_dir;

    $this->deleteProjectContent($project_id);
    $this->scanDirectory($base_dir);
  }

  /**
   * Scan a directory, starts off with the $base_dir.
   *
   * @param string $directory
   */
  protected function scanDirectory(string $directory) {

    $directory = str_replace('//', '/', $directory);

    $directory_id = bead_directory_get_id($this->project_id, $this->base_dir, $directory);
    if ($directory_id === FALSE) { // If no directory_id, create directory id.
      $directory_id = bead_directory_create($this->project_id, $this->base_dir, $directory);
    }

    $files = scandir($directory);

    foreach ($files as $file) {
      if ($file === '.' || $file === '..') { continue; }

      if (is_dir($directory . '/' . $file)) {
        $dir_decend = str_replace('//', '/', $directory . '/' . $file);
        $this->scanDirectory($dir_decend);

      } elseif (is_file($directory . '/' . $file)) {
        $this->scanFile($directory, $directory_id, $file);

      } else {
        \Drupal::messenger()->addMessage('Not a file or directory: ' . $directory . '/' . $file);
      }
    }
  }

  protected function scanFile(string $directory, int $directory_id, string $file) {
    $file_id = bead_file_get_id($directory_id, $file);
    if ($file_id === FALSE) { // If the file hasn't been analyzed, then analyze it.
      $file_id = bead_file_create($directory_id, $file);
      // Analyze file.
      $this->analyzeFile($directory, $directory_id, $file, $file_id);
    }
  }

  /**
   * Analyze a file.
   *
   * @param string $directory
   * @param int $directory_id
   * @param string $filename
   * @param int $filename_id
   *
   * @return mixed
   */
  public function analyzeFile(string $directory, int $directory_id, string $filename, int $filename_id) {

    $file_ext = pathinfo($filename, PATHINFO_EXTENSION);

    $allowed_exts = $this->getAllowedFileExtensions();
    if (!in_array($file_ext, $allowed_exts)) { return; }

    try {
      $ast = ast\parse_file(str_replace('//', '/', $directory . '/' . $filename), 70);
      $this->analyze($ast, $directory, $directory_id, $filename, $filename_id);
    } catch (\Exception $e) {
      \Drupal::messenger()->addError($e->getMessage());
    }
  }

  /**
   * @param int $nid - project id
   */
  public function deleteProjectContent(int $nid) {

    $directories = \Drupal::database()->select('bead_directories', 'dad')
      ->fields('dad', ['directory_id'])
      ->condition('project_id', $nid)
      ->execute()->fetchCol();

    $files = [];
    if (!empty($directories)) {
      $files = \Drupal::database()->select('bead_files', 'daf')
        ->fields('daf', ['file_id'])
        ->condition('directory_id', $directories, 'IN')
        ->execute()->fetchCol();
    }

    $this->doDelete($directories, $files);

    if (!empty($files)) {
      \Drupal::database()->delete('bead_files')
        ->condition('file_id', $files, 'IN')
        ->execute();
    }

    if (!empty($directories)) {
      \Drupal::database()->delete('bead_directories')
        ->condition('directory_id', $directories, 'IN')
        ->execute();
    }
  }

  abstract protected function doDelete($directories, $files);
  abstract protected function handleFunctionCall($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id, $name, $function_decl_id);

  abstract protected function getAllowedFileExtensions();
  abstract protected function analyze($ast, $directory, $directory_id, $filename, $filename_id);
}
