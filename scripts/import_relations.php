<?php
    // This script imports a set of relations from CSV format (it explodes each string on ';').
    // Simply specify by index which field should be used as source and which as target.
    // Also define by machine name the relation type you want to upload.

    // The script does not break if an error occurs with one of the relations.

    define(INPUT_FILE, '/hum/web/artechne.tst.hum.uu.nl/htdocs/sites/all/themes/artechne/scripts/outputfile.txt');
    define(SOURCE_INDEX, 1);
    define(TARGET_INDEX, 0);    
    define(RELATION_TYPE, 'appellation_recipes');

    function main() {
        $input_lines = file(INPUT_FILE);

        foreach ($input_lines as $input_line) {
            $ids = explode(";", $input_line);

            $source_id = strip_whitespaces($ids[SOURCE_INDEX]);
            $target_id = strip_whitespaces($ids[TARGET_INDEX]);

            $source_node = node_load($source_id);
            $target_node = node_load($target_id);

            // print_r($target_node);
            
            add_relation(RELATION_TYPE, $source_node, $target_node);            
        }
    } 
    
    function add_relation($relation_type, $node1, $node2) {        
        $endpoints = array();
        $endpoints[] = array('entity_type' => 'node', 'entity_id' => $node1->nid);
        $endpoints[] = array('entity_type' => 'node', 'entity_id' => $node2->nid);
        
        try {
            $r = relation_create($relation_type, $endpoints);
            $rid = relation_save($r);
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    function strip_whitespaces($line) {
        return trim(str_replace(array("\r", "\n"), '', $line));
    }

    main();
?>