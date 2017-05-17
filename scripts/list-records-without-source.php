#!/usr/bin/env drush

<?php
// This scripts retrieves all records not linked to a Source, and optionally turns them into drafts
$to_draft = drush_get_option('to_draft', FALSE);

// Retrieve all records
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
	->entityCondition('bundle', 'recipes');
$result = $query->execute();

// Print total
echo "Found " . count($result['node']) . " nodes.\n";

// Main loop
$i = 0;
foreach ($result['node'] as $node)
{
	$n = node_load($node->nid);

	// Try to fetch the linked Source
	$source = relation_get_related_entity('node', $node->nid, 'is_documented_in');

	if (!$source)
	{
		echo "No Source found for Record with id $node->nid\n";

		if ($to_draft)
		{
			$n->status = 0;
			node_save($n);
		}
	}

	//if ($i == 100) break;

	$i++;
}

echo "Finished\n";
