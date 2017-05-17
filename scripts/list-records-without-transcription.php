<?php
// This scripts retrieves all records without transcription, and optionally turns them into drafts
$to_draft = drush_get_option('to_draft', FALSE);

// Unfortunately, this requires two queries, see https://drupal.stackexchange.com/a/49834 for details.
// Retrieve all records WITH transcription
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
	->entityCondition('bundle', 'recipes')
	->fieldCondition('field_transcription', 'value', 'NULL', '!=');
$r = $query->execute();

if ($r['node'])
{
	// Retrieve all entities WITHOUT transcription
	$query = new EntityFieldQuery();
	$query->entityCondition('entity_type', 'node')
		->entityCondition('bundle', 'recipes')
		->entityCondition('entity_id', array_keys($r['node']), 'NOT IN')
		->propertyOrderBy('nid', 'ASC');
	$result = $query->execute();

	// Print total
	echo "Found " . count($result['node']) . " nodes.\n";

	// Main loop
	$i = 0;
	foreach ($result['node'] as $node)
	{
		$n = node_load($node->nid);

		echo implode(';', array($n->nid, $n->title));
		echo "\n";

		if ($to_draft)
		{
			$n->status = 0;
			node_save($n);
		}

		// if ($i == 10) break;
		
		$i++;
	}

	echo "Finished\n";
}
else
{
	echo "No nodes found\n";
}
