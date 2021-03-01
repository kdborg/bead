<?php


namespace Drupal\bead;

use ast;

class Drupal7Project extends PHPProject {
private $seen = FALSE;

  /**
   * {@inheritDoc}
   */
  protected function getAllowedFileExtensions() {
    return ['php', 'inc', 'install', 'module'];
  }

  protected function handleFunctionCall($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id, $name, $function_decl_id) {

    switch ($name) {
      case 'drupal_load':
      case 'module_load':
      case 'module_load_all':
      case 'module_load_all_includes':
      case 'module_load_install':
      case 'module_load_include':
//        print $name . ' is not supported yet.' . PHP_EOL;
//        print print_r($ast, TRUE) . PHP_EOL;
        return FALSE;
    }

    return FALSE;
  }
}
