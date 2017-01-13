<?php
// This scripts links specific recipes to a source by creating a relation.

$RECORD_TITLE_START = 'SB';
$SOURCE_ID = '92385';

// Retrieve all recipes starting with $RECORD_TITLE_START 
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
	->entityCondition('bundle', 'recipes')
	->propertyCondition('title', $RECORD_TITLE_START, 'STARTS_WITH');
$result = $query->execute();

// Print total
echo "Found " . count($result['node']) . " nodes.\n";

// Retrieve source
$s = node_load($SOURCE_ID);

// Main loop
$i = 0;
foreach ($result['node'] as $node)
{
	$n = node_load($node->nid);
	echo "Now processing node " . $n->nid . "\n";

	// Add a relation to the source
	$endpoints = array();
	$endpoints[] = array('entity_type' => 'node', 'entity_id' => $n->nid);
	$endpoints[] = array('entity_type' => 'node', 'entity_id' => $s->nid);

	$r = relation_create('is_documented_in', $endpoints);
	$rid = relation_save($r);

	// if ($i == 3) break;
	
	$i++;
}

echo "Finished\n";

