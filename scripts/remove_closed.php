<?php



$node_type = 'recipes';
$result = db_select('node', 'n')
          ->fields('n', array('nid'))
          ->condition('type', $node_type, '=')
          ->execute();

dpm($result);