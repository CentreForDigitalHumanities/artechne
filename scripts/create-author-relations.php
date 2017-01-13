<?php
// This scripts moves author data from sources to a person-source relation.

// Retrieve all recipes with field_key_cologne filled
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
	->entityCondition('bundle', 'source_codex')
	->fieldCondition('field_author', 'value', '', '<>')
	->fieldOrderBy('field_author', 'value', 'ASC');
$result = $query->execute();

// Print total
echo "Found " . count($result['node']) . " nodes.\n";

// Retrieves author for a node
function author($node) {
        return $node->field_author['und'][0]['value'];
}

// Main loop
$i = 0;
foreach ($result['node'] as $node)
{
	$n = node_load($node->nid);
	$author = author($n);
	echo "Now processing node " . $n->nid . " with author " . $author . "\n";

	// Try to fetch the person from the database
	$query = new EntityFieldQuery();
	$query->entityCondition('entity_type', 'node')
        	->entityCondition('bundle', 'person')
	        ->propertyCondition('title', $author);
	$result = $query->execute();
	
	if (count($result) > 1)
	{
		echo "Multiple results found for author, stopping script\n";
		break;
	}
	else if (count($result) == 1)
	{
		echo "Author already exists, appending relation\n";
		$a = node_load(array_shift(array_keys($result['node'])));
	}
	else
	{
		echo "Author does not yet exist, creating new node\n";
		$a = new stdClass();
		$a->title = author($n);
		$a->type = 'person';
		node_object_prepare($a); // Sets some defaults. Invokes hook_prepare() and hook_node_prepare().
		$a->language = LANGUAGE_NONE; // Or e.g. 'en' if locale is enabled
		$a->status = 1; //(1 or 0): published or not
		$a->promote = 0; //(1 or 0): promoted to front page
		$a->comment = 1; // 0 = comments disabled, 1 = read only, 2 = read/write

		$a = node_submit($a); // Prepare node for saving
		node_save($a);
	}

	// Add a relation to the author
	$endpoints = array();
	$endpoints[] = array('entity_type' => 'node', 'entity_id' => $a->nid);
	$endpoints[] = array('entity_type' => 'node', 'entity_id' => $n->nid);

	$r = relation_create('has_role_in', $endpoints);
	$r->field_role['und'][0]['value'] = 'author';
	$rid = relation_save($r);

	// if ($i == 10) break;
	
	$i++;
}

echo "Finished\n";

