<?php

/**
 * Implements hook_views_data().
 */
function bead_views_data() {
  $data = [];

  $data['project_types'] = [
    'table' => [
      'group' => t('Project Types'),
      'base' => [
        'title' => t('Project Types'),
        'help' => t('Project types provided.'),
        'query_id' => 'project_types',
      ],
    ],
    'project_type_id' => [
      'title' => t('Project type id'),
      'help' => t('THe project type id.'),
      'field' => [
        'id' => 'numeric',
      ],
    ],
    'name' => [
      'title' => t('Project type name'),
      'help' => t('The project type name.'),
      'field' => [
        'id' => 'standard',
      ],
    ],

  ];

  return $data;
}
