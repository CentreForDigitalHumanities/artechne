<?php
    // This script will remove duplicate historical names and move all existing relations (to current names and records) to the one instance that remains. 
    // Note that duplicate means that they have the exact same title (the script does apply trim and strtolower to these titles for comparison).      

    function main() {
        // Retrieve all Historical names (ordered by title)
        $all_historical_names = get_historical_names();

        // Print total
        echo "Found " . count($all_historical_names['node']) . " historical names before removing duplicates.\n";

        // Main loop
        $i = 0;
        
        $previous_node = null;
        
        foreach ($all_historical_names['node'] as $node)
        {
            $n = node_load($node->nid);
            $title = strip_newlines($n->title);

            if ($title == '') continue;

            if (strtolower(trim($title)) == strtolower(trim($previous_node->title))) {
                // import relations from previous to current node
                echo "Now copying relations from " . strip_newlines($previous_node->title) . " to " . $title . "\n";                
                copy_relations($previous_node, $n);
            }

            $previous_node = $n;
            
            // if ($i == 100) break;

            $i++;
        }
    }

    // get historical names ordered by title
    function get_historical_names() {
        $query = new EntityFieldQuery();
        $query->entityCondition('entity_type', 'node')
            ->entityCondition('bundle', 'appellation')
            ->propertyOrderBy('title', 'value');       
        $result = $query->execute();
        return $result;
    }
    
    function copy_relations($source_node, $target_node) {
        $historicalname_record_relation_type = "appellation_recipes";
        $historicalname_currentname_relation_type = "glossary_appellation";
        
        $related_records= get_related_nodes($historicalname_record_relation_type, $source_node->nid);

        foreach ($related_records as $related_record) {
            add_relation($historicalname_record_relation_type, $target_node, $related_record);
        }

        $related_current_names = get_related_nodes($historicalname_currentname_relation_type, $source_node->nid);        

        foreach ($related_current_names as $related_current_name) {
            // Note that this type of relation should be created with current name as source
            add_relation($historicalname_currentname_relation_type, $related_current_name, $target_node);
        }

        node_delete($source_node->nid);
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
                        array_push($result, $related_node);
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

    function add_relation($relation_type, $node1, $node2) {        
        $endpoints = array();
        $endpoints[] = array('entity_type' => 'node', 'entity_id' => $node1->nid);
        $endpoints[] = array('entity_type' => 'node', 'entity_id' => $node2->nid);
    
        
        $r = relation_create($relation_type, $endpoints);
        $rid = relation_save($r);
    }

    function strip_newlines($line) {
        return str_replace(array("\r", "\n"), '', $line);
    }

    main();
    echo "Found " . count(get_historical_names()['node']) . " historical names after removing duplicates.\n";    
    echo "Finished.\n";
?>