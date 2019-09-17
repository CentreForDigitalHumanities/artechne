<?php

define(EXPORT_BASE, '/relevant/folder/');
define(EXPORT_DIR, 'dans_export');


#####
# Export nodes
#####

function export_nodes($nodes, $type) {
    $export_dir_base = create_dir(EXPORT_BASE, EXPORT_DIR);
    $nodes_dir = create_dir($export_dir_base, 'nodes');
    
    // Index to limit foreach (for testing purposes)
    $i = 0;

    foreach ($nodes as $node) {
        $n = node_load($node->nid);
        
        export($n, $nodes_dir, $type);

        // if ($i == 3) break;
	    $i++;
    }
}

function export_text_fields($node, $target_folder) {
    $transcription = $node->field_transcription['und'][0]['value'];
    if ($transcription) {
        $file_transcription = $target_folder . 'transcription.txt';
        write_to_file($file_transcription, $transcription);
    }

    $translation = $node->field_translation['und'][0]['value'];
    if ($translation) {
        $file_translation = $target_folder . 'translation.txt';
        write_to_file($file_translation, $translation);
    }
}

function export_report($node, $target_folder) {
    $report = $node->body['und'][0]['value'];
    if ($report) {
        $file_report = $target_folder . 'report.html';
        write_to_file($file_report, $report);
    }
}

function export($node, $export_dir_base, $type) {    
    $type_dir = create_dir($export_dir_base, $type);
    $node_dir = create_dir($type_dir, $node->nid);
    
    export_json($node, $node_dir);

    if ($type == 'recipes') {
        export_images($node, $node_dir);
        export_text_fields($node, $node_dir);
    }

    if ($type == 'reconstruction') {
        export_images($node, $node_dir);
        export_report($node, $node_dir);
    }
}

function export_json($node, $target_folder) {
    $file_name = $node->nid . '.json';
    $file = $target_folder . $file_name;
    write_to_file($file, json_encode($node, JSON_UNESCAPED_UNICODE));
}

// This method extracts all image fields, stores the images next to the node's json file in a folder called images
// field_annotations, field_image, field_marginalia, field_traces,
function export_images($node, $node_folder)
{
    $image_dir = create_dir($node_folder, "images");
    
    $field_annotations = $node->field_annotations['und'];
    if (!is_null($field_annotations)) {
        export_images_from_node($field_annotations, $image_dir);
    }

    // field image exists on both recipes and reconstructions
    $field_image = $node->field_image['und'];
    if (!is_null($field_image)) {
        export_images_from_node($field_image, $image_dir);
    }

    $field_marginalia = $node->field_marginalia['und'];
    if (!is_null($field_marginalia)) {
        export_images_from_node($field_marginalia, $image_dir);
    }

    $field_traces = $node->field_traces['und'];
    if (!is_null($field_traces)) {
        export_images_from_node($field_traces, $image_dir);
    }
}

function export_images_from_node($field_values, $image_dir)
{
    foreach ($field_values as $field_value) {        
        // Call below downloads file and places it in $destination
        $file_path = system_retrieve_file(file_create_url($field_value['uri']), $destination = $image_dir, $managed = False, $replace = FILE_EXISTS_RENAME);        
    }
}

####
# Export relations
####
function export_relations() {
    $types = ['glossary_appellation', 'glossary_recipes', 'appellation_recipes', 'is_reconstruction_of', 'has_role_in', 'is_applied_in', 'is_reconstructed_by', 'is_documented_in', 'is_created_by'];

    foreach ($types as $type) {
        $export_dir_base = create_dir(EXPORT_BASE, EXPORT_DIR);
        $relations_dir = create_dir($export_dir_base, 'relations'); 
        
        foreach (get_relations($type) as $relation) {
            $type_dir = create_dir($relations_dir, $type);
            export_relation($relation, $type_dir);
        }
    }
}

function get_relations($type) {
    $query = relation_query();
    $query->propertyCondition('relation_type', $type);
    $relations = $query->execute();

    echo "Found " . count($relations) . " nodes of type: " . $type . "\n";

    return relation_load_multiple(array_keys($relations));
}

function export_relation($relation, $type_dir) {
    $file_name = $relation->rid . '.json';
    $file = $type_dir . $file_name;
    write_to_file($file, json_encode($relation));
}


####
# Helper functions
####
function get_nodes($type) {
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'node')
      ->entityCondition('bundle', $type);
    //   ->propertyCondition('nid', '87588'); // '87588'94336          
    $result = $query->execute();
    
    echo "Found " . count($result['node']) . " nodes of type: " . $type . "\n";
    
    return $result['node'];
}

function strip_newlines($line) {
    return str_replace(array("\r", "\n"), '', $line);
}

function write_to_file($file, $text) {    
    // echo $text . "\n";
    
    file_put_contents($file, $text);
}

function create_dir($base, $name) {
    $dir = $base . $name . '/';

    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    return $dir;
}

####
# Main entry point
####
function main() {
    $types = ['person', 'recipes', 'appellation', 'glossary', 'source_codex', 'reconstruction', 'artwork'];
    
    foreach ($types as $type) {
        $nodes = get_nodes($type);
        export_nodes($nodes, $type);
    }

    export_relations();
}

main();
