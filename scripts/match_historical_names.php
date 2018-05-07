<?php
    // This script matches a list of materials (i.e. historical names) per record 
    // to historical names in the Artechne database. If a historical name is not found,
    // the script tets if the current term exists as a current name. If it doesn't, 
    // a new historical name is added. Moreover, the script produces a list (in the output file)
    // of record ids and historical name id (either the new one or existsing).

    // The script was written to import data from the MSW dataset.

    // Path to the input file
    define(INPUT_FILE, '/hum/web/artechne.tst.hum.uu.nl/htdocs/sites/all/themes/artechne/scripts/MaterialsUsedPerRecord.csv');
    // Path to output file
    define(OUTPUT_FILE, '/hum/web/artechne.tst.hum.uu.nl/htdocs/sites/all/themes/artechne/scripts/outputfile.txt');

    function main() {
    $input_lines = file(INPUT_FILE);
    
    $i = 0;

    foreach ($input_lines as $input_line) {
    
            $record = str_getcsv($input_line, ';');
            
            $record_node_id = $record[1];

            // loop through historical names (starting from index 3)
            for ($x = 3; $x <= count($record); $x++) {
                $current_hn = strip_whitespaces($record[$x]);

                if ($current_hn == "") break;

                echo $current_hn . "\n";

                if (strtolower($current_hn) == "water" || strtolower($current_hn) == "waater") {
                    add_to_file(OUTPUT_FILE, $record_node_id . ";" . "93188");
                    continue;
                }

                if (strtolower($current_hn) == "lood") {
                    add_to_file(OUTPUT_FILE, $record_node_id . ";" . "93178");
                    continue;
                }

                if (strtolower($current_hn) == "minium") {
                    add_to_file(OUTPUT_FILE, $record_node_id . ";" . "82590");
                    continue;
                }

                if (strtolower($current_hn) == "panel") {
                    add_to_file(OUTPUT_FILE, $record_node_id . ";" . "889039");
                    continue;
                }

                if (strtolower($current_hn) == "size") {
                    add_to_file(OUTPUT_FILE, $record_node_id . ";" . "889061");
                    continue;
                }

                if (strtolower($current_hn) == "whiting") {
                    add_to_file(OUTPUT_FILE, $record_node_id . ";" . "889067");
                    continue;
                }

                if (strtolower($current_hn) == "lead") {
                    // This matches a current name!
                    continue;
                }

                if (strtolower($current_hn) == "oil") {
                    continue;
                }

                $matching_historical_names = get_entities($current_hn, 'appellation');
                $found_matching_historical_name = false;
                $add_as_new_historical_name = false;
                
                if (count($matching_historical_names) > 0) {
                    foreach ($matching_historical_names as $mhn) {
                        $node = node_load($mhn->nid);

                        if (strtolower($current_hn) == strtolower(strip_whitespaces($node->title))) {
                            add_to_file(OUTPUT_FILE, $record_node_id . ";" . $mhn->nid);
                            $found_matching_historical_name = true;
                        }
                        else {
                            if (contains_comma($node->title) && was_confirmed($current_hn, $node->title)) {
                                add_to_file(OUTPUT_FILE, $record_node_id . ";" . $mhn->nid);
                                $found_matching_historical_name = true;
                            }
                        }
                    }
                }

                if (!$found_matching_historical_name) {                
                    $matching_current_names = get_entities($current_hn, 'glossary');
                    
                    if (count($matching_current_names) > 0) {
                        foreach ($matching_current_names as $mcn) {
                            $node = node_load($mcn->nid);

                            if (strtolower($current_hn) == strtolower(strip_whitespaces($node->title))) {
                                $add_as_new_historical_name = false;
                                break;
                            }

                            if (contains_comma($node->title) && was_confirmed($current_hn, $node->title)) {                            
                                $add_as_new_historical_name = false;
                                break;
                            }
                            else {
                                $add_as_new_historical_name = true;
                            }
                        }
                    }
                    else {
                        $add_as_new_historical_name = true;
                    }
                }

                if ($add_as_new_historical_name) {
                    $new_nid = add_new_historical_name($current_hn); 
                    add_to_file(OUTPUT_FILE, $record_node_id . ";" . $new_nid);
                }
                // print_r($matching_historical_names);
            }

            // if ($i > 3) break;
            $i++;
        }
    }

    function contains_comma($string) {
        return strpos($string, ',') !== false;    
    }

    function add_new_historical_name($title) {
        echo "Creating new node for: " . $title . "\n";
		$a = new stdClass();
		$a->title = $title;
		$a->type = 'appellation';
		node_object_prepare($a); // Sets some defaults. Invokes hook_prepare() and hook_node_prepare().
		$a->language = LANGUAGE_NONE; // Or e.g. 'en' if locale is enabled
		$a->status = 1; //(1 or 0): published or not
		$a->promote = 0; //(1 or 0): promoted to front page
		$a->comment = 1; // 0 = comments disabled, 1 = read only, 2 = read/write

		$a = node_submit($a); // Prepare node for saving
        node_save($a);
        
        return $a->nid;
    }

    function was_confirmed($hn, $match) {
        $message = "Is this: " . $hn . " - " . $match . " a match?";
        print $message;
        flush();
        ob_flush();
        $confirmation  =  trim( fgets( STDIN ) );
        if ( $confirmation === 'y' ) {
            return true;
        }
        return false;
    }

    function add_to_file($file, $line) {
        if (file_exists($file)) {
            // Open the file to get existing content
            $current = file_get_contents($file);
        }
        else {
            $current = "";
        }        
        
        $current .= $line . "\n";
        file_put_contents($file, $current);
    }

    function get_entities($title, $content_type) {
        $query = new EntityFieldQuery();
        $query->entityCondition('entity_type', 'node')
          ->entityCondition('bundle', $content_type)
          ->propertyCondition('title', $title, 'CONTAINS');          
        $result = $query->execute();
        
        // echo "Found " . count($result['node']) . " nodes\n";
        
        return $result['node'];
    }

    function strip_whitespaces($line) {
        return trim(str_replace(array("\r", "\n"), '', $line));
    }

    main();    
?>