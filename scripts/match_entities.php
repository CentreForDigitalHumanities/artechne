<?php
    // This script reads lines from a file and searches the Artechne database for entries with a title matching the line.
    // What 'matching' means should be configured below, as some other things.
    // This will result in two csv files
    // 1) OUTPUT_ALL will contain all lines, with one of three things in a second column:
    //          - the node id of the matching node
    //          - a new node id (will be created if no match is found)
    //          - UNKNOWN if input is invalid (see below: EXCLUDE_LINES_THAT_START_WITH), or multiple nodes were found
    // 2) OUTPUT_TO_BE_CONSIDERED will contain for which node id is UNKNOWN 
    // 
    // Contains lines with entity titles to be checked
    define(INPUT_FILE, '');
    
    // Each line that produces ambivalent output will be stored here
    define(OUTPUT_TO_BE_CONSIDERED, '');
    
    // Each line will be output to this file.
    // Entities that were found will include their node id.
    // Entities added will include their (new) node id
    // Entities for which no id could be established autmatically will have 'UNKNOWN' as the value for id
    define(OUTPUT_ALL, '');

    // The type of entity you want to check against (should be the machine name)
    define(CONTENT_TYPE, 'source_codex');

    // Operator used when querying the db
    define(EQUALITY_OPERATOR, "CONTAINS"); // OR: STARTS_WITH (remember we are comparing the title property)

    // Character used to split the line.
    // The first value in the resulting array will subsequently be used to query for entities again.
    // Results will always end up in OUTPUT_TO_BE_CONSIDERED.
    // Make it null to skip this extra search
    define(EXPLODE_CHARACTER, null);
    
    // A list of strings used to validate input. If an input line starts with one of these strings,
    // it is written straight to TO_BE_CONSIDERED (and to ALL with UNKNOWN) 
    define (EXCLUDE_LINES_THAT_START_WITH, serialize (array()));

    // New nodes will be created with IDs starting from this number.
    // Make sure they will be unique!
    // Nice to create different kinds of entities with typical node ids (e.g. all 'Sources' added by the script start with 555).
    define(START_NEW_IDS_FROM, 666000);

    // Globals that will hold state
    $PREVIOUS_INPUT = null;
    $PREVIOUS_OUTPUT = null;
    $LATEST_NEW_ID = null;
    
    function match_entities() {
        global $PREVIOUS_INPUT;
        global $PREVIOUS_OUTPUT;
        
        $input_lines = file(INPUT_FILE);

        foreach ($input_lines as $input_line) {
            echo "Matching: " . $input_line;

            $input_line = strip_newlines($input_line);

            // Validate line!
            if (!is_valid_input($input_line)) {
                $output_line = $input_line  . ";UNKNOWN;";
                add_to_file(OUTPUT_ALL, $output_line);
                $PREVIOUS_INPUT = $input_line;
                continue;
            }
            
            if ($input_line == $PREVIOUS_INPUT) {
                add_to_file(OUTPUT_ALL, $PREVIOUS_OUTPUT);
                $PREVIOUS_INPUT = $input_line;
                continue;
            }
           
            $entity_exists_in_db = entity_exists($input_line);
                        
            if ($entity_exists_in_db) {
                handle_found($input_line);
            }
            else {
                if (!is_null(EXPLODE_CHARACTER)) {                    
                    $parts = explode(EXPLODE_CHARACTER, $input_line);
                    $entity_exists_in_db = entity_exists($parts[0]);

                    if ($entity_exists_in_db) { // if something is found
                        handle_to_be_considered($parts[0], $input_line);
                        $PREVIOUS_INPUT = $input_line;
                        continue;
                    }
                }
                
                handle_not_found($input_line);
                
            }

            $PREVIOUS_INPUT = $input_line;
        }
    }
    
    function is_valid_input($input_line) {        
        foreach (unserialize(EXCLUDE_LINES_THAT_START_WITH) as $exclude) {
            if (strtolower(substr($input_line, 0, strlen($exclude))) == strtolower($exclude)) {
                return false;
            }
        }
        
        if ($input_line == '') {
            return false;
        }

        return true;
    }

    function handle_found($line) {
        $entities = get_entities($line);

        if (is_array($entities)) {
            if (count($entities) > 1) {
                $output_line = get_line_with_node_ids(null, $line, $entities);
                add_to_file(OUTPUT_ALL, 'UNKNOWN;');
                add_to_file(OUTPUT_TO_BE_CONSIDERED, $output_line);
            }
            else {
                $output_line = $line . ";" . get_node_id($entities) . ";";
                add_to_file(OUTPUT_ALL, $output_line);
            }
        }
    }

    function get_node_id($entities) {
        $node_id = null;

        foreach ($entities as $entity) {
            $node_id = $entity->nid;
        }

        return $node_id;
    }

    function handle_not_found($line){
        global $LATEST_NEW_ID;
        
        if ($LATEST_NEW_ID) {
            $new_id = $LATEST_NEW_ID + 1;            
        }
        else {
            $new_id = START_NEW_IDS_FROM;
        }
        $LATEST_NEW_ID = $new_id;
                
        $line = $line . ";" . $new_id . ";";
        add_to_file(OUTPUT_ALL, $line);
    }

    function handle_to_be_considered($part, $line) {
        $entities = get_entities($part);

        if (is_array($entities)) {
            $output_line = get_line_with_node_ids($part, $line, $entities);            
            add_to_file(OUTPUT_TO_BE_CONSIDERED, $output_line);
            
            $output_line = $line . ";UNKNOWN;"; 
            add_to_file(OUTPUT_ALL, $output_line);     
        }  
    }

    function get_line_with_node_ids($part, $input_line, $entities) {
        if ($part) {
            $line = $part . "; " . $input_line . ";";
        }
        else {
            $line = $input_line . ";";
        }
        
        foreach ($entities as $entity) {
            $node = node_load($entity->nid);
            $line .= $node->title . "(" . $node->nid . "), ";
        }

        $line .= ";";

        return $line;
    }

    function add_to_file($file, $line) {
        global $PREVIOUS_OUTPUT;

        if (file_exists($file)) {
            // Open the file to get existing content
            $current = file_get_contents($file);
        }
        else {
            $current = "";
        }        
        
        $current .= $line . "\n";
        file_put_contents($file, $current);

        $PREVIOUS_OUTPUT = $line;
    }
    
    function entity_exists($title) {
        $result = get_entities($title);
        return count($result) > 0;
    }
    
    function get_entities($title) {
        $query = new EntityFieldQuery();
        $query->entityCondition('entity_type', 'node')
          ->entityCondition('bundle', CONTENT_TYPE)
          ->propertyCondition('title', $title, EQUALITY_OPERATOR);          
        $result = $query->execute();
        
        echo "Found " . count($result['node']) . " nodes\n";
        
        return $result['node'];
    }

    function strip_newlines($line) {
        return str_replace(array("\r", "\n"), '', $line);
    }

    match_entities();
?>