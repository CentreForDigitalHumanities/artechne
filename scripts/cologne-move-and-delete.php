<?php
// This scripts moves data from Cologne recipes to existing recipes and then removes them.

// Retrieve all recipes with field_key_cologne filled
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
	->entityCondition('bundle', 'recipes')
	->fieldCondition('field_key_cologne', 'value', '', '<>')
	->fieldOrderBy('field_key_cologne', 'value', 'ASC');
$result = $query->execute();

// Print total
print count($result['node']) . "\n";

// Retrieves field_id for a node
function field_id($node) {
	return $node->field_id['und'][0]['value'];
}

// Retrieves key_cologne for a node
function key_cologne($node) {
	return $node->field_key_cologne['und'][0]['value'];
}

// Retrieves source_recipe_location for a node
function recipe_location($node) {
	return $node->field_source_recipe_location['und'][0]['value'];
}

// Retrieves artistic_contents for a node
function artistic_contents($node) {
	return $node->field_artistic_contents['und'][0]['value'];
}

// Retrieves artistic_techniques for a node
function artistic_techniques($node) {
	return $node->field_artistic_techniques['und'][0]['value'];
}

// Main loop
$i = 0;
$prev = NULL;
foreach ($result['node'] as $node) {
	$n = node_load($node->nid);
	//if ($i == 1) var_dump($n);
	
	//if (intval(key_cologne($n)) > 1225) break;
	var_dump(key_cologne($n) . ' ' . $node->nid);
	
	if ($prev && key_cologne($prev) == key_cologne($n)) {
		if (field_id($prev)) {
			$new = $prev;
			$old = $n;
		}
		else {
			$new = $n;
			$old = $prev;
		}
		
		var_dump(recipe_location($old) . ' => ' . recipe_location($new));
		var_dump(artistic_contents($old) . ' => ' . artistic_contents($new));
		var_dump(artistic_techniques($old) . ' => ' . artistic_techniques($new));
		
		//$new->field_source_recipe_location['und'][0]['value'] = recipe_location($old);
		$new->field_artistic_contents['und'][0]['value'] = artistic_contents($old);
		$new->field_artistic_techniques['und'][0]['value'] = artistic_techniques($old);
		node_save($new);
		node_delete($old->nid);
	}
	else {	
		$prev = $n;
	}

	$i++;
}

print("Finished\n");

