<?php

define(EXPORT_BASE, '/home/ahebing/');
define(EXPORT_DIR, 'export26112018');

function get_nodes($type) {
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'node')
      ->entityCondition('bundle', $type);
    //   ->propertyCondition('nid', '87588');          
    $result = $query->execute();
    
    echo "Found " . count($result['node']) . " nodes of type: " . $type . "\n";
    
    return $result['node'];
}

function export_nodes($nodes, $type) {
    $export_dir_base = create_dir(EXPORT_BASE, EXPORT_DIR);
    
    // Index to limit foreach (for testing purposes)
    $i = 0; 

    foreach ($nodes as $node) {
        $n = node_load($node->nid);
        $file_name = $n->nid . '.json';
        
        export($n, $export_dir_base, "individual", $type, $file_name);

        if (has_getty_ref($n)) { 
            export($n, $export_dir_base, "getty_linked", $type, $file_name);
        }

        // if ($n->type == 'recipes') {            
        //     export_transcription($n, $export_dir_base, $type);
        // }

        // if ($i == 3) break;
	
	    $i++;
    }
}

function export($node, $export_dir_base, $subfolder_name, $type, $file_name) {    
    $dest_dir_base = create_dir($export_dir_base, $subfolder_name);
    $dest_dir = create_dir($dest_dir_base, $type);    
    $file = $dest_dir . $file_name;
    write_to_file($file, json_encode($node));
}

function export_transcription($node, $export_dir_base, $type) {
    $dest_dir_base = create_dir($export_dir_base, 'transcription');
    $dest_dir = create_dir($dest_dir_base, $node->nid);
    $file_metadata = $dest_dir . 'metadata.json';
    $file_transcription = $dest_dir . 'transcription.txt';
    $file_historical_names = $dest_dir . 'historical_names.txt';

    $metadata = get_metadata($node);
    write_to_file($file_metadata, json_encode($metadata));
    write_to_file($file_transcription, $node->field_transcription['und'][0]['value']);
    write_to_file($file_historical_names, get_historical_names($node));
}

function get_metadata($node) {
    return (object) [
        'nid' => $node->nid,
        'title' => $node->title,
        'source' => $node->field_source_title_comp['und'][0]['value'],
        'pages' => $node->field_page_numbers['und'][0]['value'],
        'year' => $node->field_year['und'][0]['value'],  
        'author' => $node->field_source_author['und'][0]['value'],
        'language' => $node->field_language['und'][0]['value'],
    ];
}

function get_historical_names($node) {
    $related_hns = get_related_nodes('appellation_recipes', $node->nid);
    return implode(PHP_EOL, $related_hns);
}

function get_related_nodes($relation_type, $node_id) {         
    $relation_list = relation_load_multiple(array_keys(query_for_relations($relation_type, $node_id)));
    $related_nodes = extract_related_nodes($relation_list, $node_id);    
    return $related_nodes;
}
function query_for_relations($relation_type, $node_id) {        
    $query = relation_query();
    $query->related('node', $node_id, NULL);
    $query->propertyCondition('relation_type', $relation_type);
    $result = $query->execute();
    return $result;
}
function extract_related_nodes($relation_list, $node_id) {
    $result = array();
    foreach ($relation_list as $relation) {
        foreach (relation_get_endpoints($relation, 'node') as $related_nodes) {
            foreach ($related_nodes as $related_node) {                    
                if ($related_node->nid <> $node_id & !relation_already_exists($result, $related_node)) {                    
                    array_push($result, trim($related_node->title,  " "));
                }
            }
        }
    }
    return $result;
}
function relation_already_exists($related_nodes, $new_related_node) {
    $result = false;
    
    foreach ($related_nodes as $related_node) {
        if (strtolower($related_node->title) == strtolower($new_related_node->title)) {
            $result = true;
            break;
        }
    }
    return $result;
}
function strip_newlines($line) {
    return str_replace(array("\r", "\n"), '', $line);
}

function has_getty_ref($node) {
    return count($node->field_getty_id) > 0;
}

function create_dir($base, $name) {
    $dir = $base . $name . '/';

    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    return $dir;
}

function write_to_file($file, $text) {    
    file_put_contents($file, $text);
}

function main() {
    $types = ['person', 'recipes', 'appellation', 'glossary', 'source_codex'];
    // $types = ['recipes'];
    
    foreach ($types as $type) {
        $nodes = get_nodes($type);
        export_nodes($nodes, $type);
    }
}

main();