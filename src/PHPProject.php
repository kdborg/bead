<?php

namespace Drupal\bead;

use ast;
use ds;

class PHPProject extends ProjectBase {

  private $types = [];

  /**
   * {@inheritDoc}
   */
  protected function getAllowedFileExtensions() {

    return ['php', 'inc'];
  }

  /**
   * {@inheritDoc}
   */
  protected function analyze($ast, $directory, $directory_id, $filename, $filename_id) {
    $this->doAnalysis($ast, $directory, $directory_id, $filename, $filename_id);
    $this->display(0, 'Type counts: ' . print_r($this->types, TRUE));
//    drupal_set_message(print_r($this->types, TRUE));
  }

  /**
   * @param int id
   */
  protected function showAstNumber(int $ast_id) {
    $this->display($ast_id, ast\get_kind_name($ast_id));
  }

  protected function doAnalysis($ast, $directory, $directory_id, $filename, $filename_id) {
    $this->showAstNumber(136);
    $this->showAstNumber(773);
    $this->showAstNumber(257);
    $this->showAstNumber(2048);
    $this->showAstNumber(129);
    $this->showAstNumber(132);
    $this->showAstNumber(69);
    $this->showAstNumber(70);
    $this->showAstNumber(767);

    $deque = new \Ds\Deque();
    $deque->push($ast);
    while (!$deque->isEmpty()) {
      $ast = $deque->shift();

      $type = gettype($ast);
      if ($type == 'object') {
        $type = get_class($ast);

        if ($type == 'ast\Node') {
          $kind = ast\get_kind_name($ast->kind);
          if (isset($this->types[$kind])) {
            $this->types[$kind]++;
          }
          else {
            $this->types[$kind] = 1;
          }

          $lineno = $ast->lineno;
          $uses_flags = ast\kind_uses_flags($ast->kind);
          switch ($ast->kind) {
            case ast\AST_CALL:
              $this->handleAST_CALL($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id);
              break;
            case ast\AST_FUNC_DECL:
              $this->handleAST_FUNC_DECL($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id);
              break;
            case ast\AST_CONST:
              $this->handleAST_CONST($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id);
              break;
            case ast\AST_GLOBAL:
              $this->handleAST_GLOBAL($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id);
              break;
            case ast\AST_INCLUDE_OR_EVAL:
              $this->handleAST_INCLUDE_OR_EVAL($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id);
              break;
            case ast\AST_NAME:
            case ast\AST_ARG_LIST:
            case ast\AST_PARAM_LIST:
            case ast\AST_STMT_LIST:
            case ast\AST_RETURN:
            case ast\AST_BINARY_OP:
            case ast\AST_VAR:
            case ast\AST_ASSIGN:
            case ast\AST_DIM:
            case ast\AST_IF:
            case ast\AST_IF_ELEM:
            case ast\AST_UNARY_OP:
            case ast\AST_ISSET:
            case ast\AST_UNSET:
            case ast\AST_EMPTY:
            case ast\AST_ARRAY:
            case ast\AST_ARRAY_ELEM:
            case ast\AST_ASSIGN_OP:
            case ast\AST_PRINT:
            case ast\AST_STMT_LIST:
            case ast\AST_FOREACH:
            case ast\AST_POST_INC:
            case ast\AST_POST_DEC:
            case ast\AST_REF:
            case ast\AST_BREAK:
            case ast\AST_CAST:
            case ast\AST_CONDITIONAL:
            case ast\AST_ASSIGN_REF:
            case ast\AST_SWITCH:
            case ast\AST_SWITCH_LIST:
            case ast\AST_SWITCH_CASE:
            case ast\AST_TRY:
            case ast\AST_CATCH:
            case ast\AST_CATCH_LIST:
            case ast\AST_THROW:
            case ast\AST_CLOSURE:
            case ast\AST_INSTANCEOF:
            case ast\AST_DO_WHILE:
            case ast\AST_PRE_INC:
            case ast\AST_FOR:
            case ast\AST_WHILE:
            case ast\AST_CONTINUE:
            case ast\AST_EXPR_LIST:
            case ast\AST_ENCAPS_LIST:
            case ast\AST_ECHO:
              break;
            default:
              $this->display(0, 'Not handled: ' . $ast->kind . ': ' . $kind);

              if ($ast->kind === ast\AST_CLASS) {
                $this->display($lineno, 'CLASS DECL: ' . print_r($ast, TRUE));
              }
//              if ($ast != $this->ast) {
                //              $node = $this->createNode('not_handled', $ast->kind . ': ' . $kind);
                //              $node->save();
                //              drupal_set_message('NOT HANDLED: ' . $kind . ' ast: ' . print_r($ast, TRUE));
//              }
          }
        }
      }

      if ($type !== 'null') {
        if (isset($this->types[$type])) {
          $this->types[$type]++;
        }
        else {
          $this->types[$type] = 1;
        }
      }

      if (isset($ast->children)) {
        foreach ($ast->children as $child) {
          $deque->push($child);
        }
      }
    }
  }

  protected function doDelete($directories, $files) {

    if (!empty($files)) {

      $func_ids = \Drupal::database()->select('bead_function_decl', 'bfd')
        ->fields('bfd', ['function_decl_id'])
        ->condition('file_id', $files, 'IN')
        ->execute()->fetchCol();

      if (!empty($func_ids)) {
        $tables = [
          'bead_function_decl_param',
        ];

        foreach ($tables as $table) {
          \Drupal::database()->delete($table)
            ->condition('function_decl_id', $func_ids, 'IN')
            ->execute();
        }
      }

      $tables = [
        'bead_function_ref',
        'bead_function_decl',
        'bead_constant_ref',
        'bead_constant_decl',
      ];

      foreach ($tables as $table) {
        \Drupal::database()->delete($table)
          ->condition('file_id', $files, 'IN')
          ->execute();
      }
    }
  }

  protected function display($lineno, ...$values) {

    print $lineno . ': ' . implode(', ', $values) . PHP_EOL;
//    \Drupal::messenger()->addMessage($lineno . ': ' . implode(', ', $values));
  }

  protected function handleAST_CALL($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id) {
    $name = $ast->children['expr']->children['name'];

    if ($name === 'define') {
      // Constant definition
      $def = $ast->children['args']->children[0];
      bead_constant_decl_create($filename_id, $lineno, $def);
    } else {
      // Consider $name to be calling the function.
      if (is_string($name)) {

        $function_decl_id = bead_function_decl_id_get($name);
        if ($function_decl_id === FALSE) {
          $function_decl_id = bead_function_decl_create(0, 1, $name, '');
        }

        bead_function_ref_create($filename_id, $lineno, $function_decl_id);

        $handled = $this->handleFunctionCall($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id, $name, $function_decl_id);
//        if (!$handled) {
//          $this->display($lineno, 'function_call', $name);
//        }
      } else {
        $this->display($lineno, $filename . ' : ' . print_r($name, TRUE));
      }
    }
  }

  protected function handleFunctionCall($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id, $name, $function_decl_id) {
    return FALSE;
  }

  protected function handleAST_FUNC_DECL($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id) {
    $name = $ast->children['name'];
    $docComment = $ast->children['docComment'];
    if (empty($docComment)) { $docComment = ''; }

    $function_decl_id = bead_function_decl_create($filename_id, $lineno, $name, $docComment);
    $params = $ast->children['params'];

    foreach ($params->children as $param) {
      $this->display($lineno, print_r($param, TRUE));
      if ($param->kind == ast\AST_PARAM) {
        $pname = $param->children['name'];
        $constant_decl_id = 0;

        $pdefault =  $param->children['default'];
        if (isset($pdefault->kind)) {
          if ($pdefault->kind === ast\AST_CONST) {
            $pdefault_value = $pdefault->children['name']->children['name'];
          } elseif ($pdefault->kind === ast\AST_ARRAY) {
            $pdefault_value = '[]';
          } else {
            $this->display($lineno, 'Parameter type not supported: ' . $pname);
          }

          if (!empty($pdefault_value)) {
            $constant_decl_id = bead_constant_decl_get_id($pdefault_value);
            if ($constant_decl_id === FALSE) {
              $constant_decl_id = bead_constant_decl_create($filename_id, $lineno, $pdefault_value);
            }
          }
        }

        bead_function_decl_param_create($pname, $constant_decl_id, $function_decl_id);
      }
    }
  }

  protected function handleAST_CONST($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id) {
    $name = $ast->children['name']->children['name'];

    $constant_decl_id = bead_constant_decl_get_id($name);
    if ($constant_decl_id === FALSE) {
      $constant_decl_id = bead_constant_decl_create(0, 1, $name);
    }

    bead_constant_ref_create($filename_id, $lineno, $constant_decl_id);
  }

  protected function handleAST_GLOBAL($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id) {
    $name = $ast->children['var']->children['name'];

    $this->display($lineno, 'global_variable_used', $name);
  }

  protected function handleAST_INCLUDE_OR_EVAL($ast, $lineno, $uses_flags, $directory, $directory_id, $filename, $filename_id) {
    $include = $ast->children['expr']->children['right'];

    if (is_string($include)) {
      $this->display($lineno, 'include_or_eval', $include);

//      $directory = pathinfo($include, PATHINFO_DIRNAME);
//      if (in_array($directory, ['.', '..'])) { return; }
//
//      $parts = explode('/', $directory);
//      $index = 0;
//      $check_dir = '';
//      $directory_id = 0;
//      while ($index < count($parts)) {
//        $check_dir .= '/' . $parts[$index];
//        $check_dir = str_replace('//', '/', $check_dir);
//        $directory_id = bead_directory_get_id($this->project_id, $this->base_dir, $check_dir);
//        if ($directory_id === FALSE) {
//          $directory_id = bead_directory_create($this->project_id, $this->base_dir, $check_dir);
//        }
//
//        $index++;
//      }
//
//      $filename = pathinfo($include, PATHINFO_FILENAME);
//      $file_ext = pathinfo($include, PATHINFO_EXTENSION);
//      $filename .= '.' . $file_ext;
//      if (!empty($filename)) {
//        print 'Scanning into: ' . $directory . '/' . $filename . PHP_EOL;
//        print 'Return from: ' . $directory . '/' . $filename . PHP_EOL;
//      }
    } else {
      $this->display($lineno, $filename . '::: ' . print_r($include, TRUE));
    }
  }
}



