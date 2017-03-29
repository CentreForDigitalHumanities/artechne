<?php
// This scripts copies the language column from a Source (machine name: source_codex) to a Record (machine name: recipes)

// Retrieve all records
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
	->entityCondition('bundle', 'recipes');
$result = $query->execute();

// Print total
echo "Found " . count($result['node']) . " nodes.\n";

// Retrieves language for a node
function language($node)
{
	return $node->field_language['und'][0]['value'];
}

// Main loop
$i = 0;
foreach ($result['node'] as $node)
{
	// Skip nodes that already have their language specified
	$n = node_load($node->nid);
	if (language($n))
	{
		echo "Language already specified for Record with id $node->nid\n";
		continue;
	}

	// Try to fetch the linked Source
	$source = relation_get_related_entity('node', $node->nid, 'is_documented_in');
	
	if ($source)
	{
		$n->field_language['und'][0]['value'] = language($source);
		node_save($n);
	}
	else
	{
		echo "No Source found for Record with id $node->nid\n";
	}

	//if ($i == 100) break;
	
	$i++;
}

echo "Finished\n";
