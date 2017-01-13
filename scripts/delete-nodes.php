<?php
$query = new EntityFieldQuery();

$query->entityCondition('entity_type', 'node')->entityCondition('bundle', 'biblio');
  // Run the query as user 1.
$result = $query->execute();

print sizeof($result['node']);

foreach ($result['node'] as $node){
  node_delete($node->nid);
}

print("deleted");