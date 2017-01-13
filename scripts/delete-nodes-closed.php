<?php


$query = new EntityFieldQuery();

$query->entityCondition('entity_type', 'node')->entityCondition('bundle', 'recipes')->fieldCondition('field_transcription_access_statu', 'value', 'closed', '=');
  // Run the query as user 1.
$result = $query->execute();

print sizeof($result['node']);

foreach ($result['node'] as $node){
   node_delete($node->nid);
}
print("deleted");
