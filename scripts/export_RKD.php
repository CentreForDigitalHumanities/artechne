<?php

define(EXPORT_BASE, '/home/ahebing/rkd_export_1110/');
define(RKD_TERMS, '/home/ahebing/terms.json');

// store queries made to geonames (see get_source_place), 
// so we can reuse them (and don't have to call geonames for every record)
$queried_lat_lons;

$rkd_terms;

#######################
### Extract Artechne fields

// This method extracts all image fields, stores the images next to the export file, and returns all paths in a string (separated by ';')
// field_annotations, field_image, field_marginalia, field_traces,
function get_annotations($node)
{
    $results = [];
    $node_id = $node->nid;

    $field_annotations = $node->field_annotations['und'];
    if (!is_null($field_annotations)) {
        array_push($results, extract_images($field_annotations, $node_id));
    }

    $field_image = $node->field_image['und'];
    if (!is_null($field_image)) {
        array_push($results, extract_images($field_image, $node_id));
    }

    $field_marginalia = $node->field_marginalia['und'];
    if (!is_null($field_marginalia)) {
        array_push($results, extract_images($field_marginalia, $node_id));
    }

    $field_traces = $node->field_traces['und'];
    if (!is_null($field_traces)) {
        array_push($results, extract_images($field_traces, $node_id));
    }

    if (count($results) == 0) {
        return null;
    }

    return implode("; ", $results);
}

function extract_images($field_values, $node_id)
{
    $results = [];

    foreach ($field_values as $field_value) {
        // download image and store next to export
        $image_dir = create_dir(EXPORT_BASE, "images");
        $dir = create_dir($image_dir, $node_id);
        // Call below downloads file and places it in $destination
        $file_path = system_retrieve_file(file_create_url($field_value['uri']), $destination = $dir, $managed = False, $replace = FILE_EXISTS_RENAME);
        // extract filename
        $file_name = basename($file_path);
        array_push($results, "images/" . $node_id . "/" . $file_name);
    }

    return implode("; ", $results);
}

// field_starting_date, field_year
function get_starting_date($node)
{
    $results = [];

    $field_starting_date = $node->field_starting_date['und'][0]['value'];
    if (!is_null($field_starting_date)) {
        array_push($results, $field_starting_date);
    }

    $field_year = $node->field_year['und'][0]['value'];
    if (!is_null($field_year)) {
        // Use below to extract first year
        // preg_match('/\d{4}/', $field_year, $matches);
        // echo "\n\n";
        // echo $field_year . "\n";
        // print_r($matches);
        // echo "\n\n";
        array_push($results, $field_year);
    }

    if (count($results) == 0) {
        return null;
    }

    return implode(", ", $results);
}

// field_end_date, field_year
function get_end_date($node)
{
    $results = [];

    $field_end_date = $node->field_end_date['und'][0]['value'];
    if (!is_null($field_end_date)) {
        array_push($results, $field_end_date);
    }

    $field_year = $node->field_year['und'][0]['value'];
    if (!is_null($field_year)) {
        array_push($results, $field_year);
    }

    if (count($results) == 0) {
        return null;
    }

    return implode(", ", $results);
}

// field_year
function get_date($node)
{
    return $node->field_year['und'][0]['value'];
}

// field_remarks, field_id
function get_remarks($node)
{
    $results = [];

    array_push($results, "Artechne ID: " . $node->nid . "");

    $field_id = $node->field_id['und'][0]['value'];
    if (!is_null($field_id)) {
        array_push($results, "ColourContext ID: " . $field_id . "");
    }

    $field_remarks = $node->field_remarks['und'][0]['value'];
    if (!is_null($field_remarks)) {
        array_push($results, $field_remarks);
    }

    if (count($results) == 0) {
        return null;
    }

    return implode("; ", $results);
}

// field_link_to_manuscript => url checken
function get_manuscript_link($node)
{
    $url = $node->field_link_to_manuscript['und'][0]['url'];

    $result = null;

    if (!is_null($url)) {
        // if dbnl rearrange
        if (strpos($url, 'dbnl') !== false) {
            $url = rearrange_dbnl_link($url);
        }

        // check 404
        if (!yields_404($url)) {
            $result = $url;
        }
    }

    return $result;
}

function rearrange_dbnl_link($url)
{
    $result = $url;

    // on the P server there is only one source from dbnl referenced in the old style, so
    // if old url style    
    if (strpos($url, 'scan') !== false) {
        $base = "https://www.dbnl.org/tekst/mand001schi01_01/";
        $index = strrpos($url, "=");
        $detail = str_replace("scan", "", substr($url, $index + 1)) . ".php";
        $result = $base . $detail;
    }

    return $result;
}

function yields_404($url)
{
    $result = False;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_code == 404) {
        $result = True;
    }

    curl_close($ch);
    return $result;
}

// field_library, field_ms_id, field_page_numbers, field_source_title, title, => AUTEURNAAM...?
function get_title($node)
{
    $results = [];

    $title = $node->title;
    if (!is_null($title)) {
        array_push($results, $title);
    }

    $source_title = get_source_title($node);
    if (!is_null($source_title)) {
        array_push($results, $source_title);
    }

    $field_source_author = get_source_author($node);
    if (!is_null($field_source_author)) {
        array_push($results, $field_source_author);
    }

    $field_page_numbers = $node->field_page_numbers['und'][0]['value'];
    if (!is_null($field_page_numbers)) {
        array_push($results, $field_page_numbers);
    }

    $field_library = $node->field_library['und'][0]['value'];
    if (!is_null($field_library)) {
        array_push($results, $field_library);
    }

    $field_ms_id = $node->field_ms_id['und'][0]['value'];
    if (!is_null($field_ms_id)) {
        array_push($results, $field_ms_id);
    }

    if (count($results) == 0) {
        return null;
    }

    return str_replace(array("\r", "\n"), '', implode(", ", $results));
}

function get_transcription($node)
{
    $transcription = $node->field_transcription['und'][0]['value'];
    $result = str_replace(array("\r", "\n"), '', $transcription);

    if (strlen($result) == 0) {
        $result = null;
    }
    return $result;
}

function get_translation($node)
{
    $translation = $node->field_translation['und'][0]['value'];
    $result = str_replace(array("\r", "\n"), '', $translation);
    if (strlen($result) == 0) {
        $result = null;
    }
    return $result;
}

// source => field_source_location
function get_source_location($node)
{
    $result = null;

    $lat = $node->field_source_latitude['und'][0]['value'];
    $lon = $node->field_source_longitude['und'][0]['value'];

    if (!is_null($lat)) {
        $result = $lat . ", " . $lon;
    }

    return $result;
}


// source => field_source_location
function get_source_place($node)
{
    global $queried_lat_lons;
    $is_new = false;
    $result = null;

    if (!$queried_lat_lons) {
        $queried_lat_lons = [];
    }

    $lat = $node->field_source_latitude['und'][0]['value'];
    $lon = $node->field_source_longitude['und'][0]['value'];

    if (!is_null($lat)) {
        $lat_lon = $lat . $lon;

        if (array_key_exists($lat_lon, $queried_lat_lons)) {
            return $queried_lat_lons[$lat_lon];
        } else {
            $is_new = true;
        }

        $url = "http://api.geonames.org/findNearbyJSON?username=alexhebing&lat=" . $lat . "&lng=" . $lon . "&featureClass=P&featureCode=PPL&featureCode=PPLA&featureCode=PPLA2&featureCode=PPLA3&featureCode=PPLA4&featureCode=PPLC&featureCode=PPLCH";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 200 && $response) {
            $result = $response["geonames"][0]["name"];
        }

        curl_close($ch);

        if ($is_new) {
            $queried_lat_lons[$lat_lon] = $response["geonames"][0]["name"];
        }
    }

    return $result;
}

function get_source_title($node)
{
    $source = relation_get_related_entity('node', $node->nid, 'is_documented_in', 0);
    if ($source) return $source->title;
    else return null;
}

function get_source_author($node)
{
    $authors = array();

    $source = relation_get_related_entity('node', $node->nid, 'is_documented_in', 0);

    if ($source) {
        $query = relation_query('node', $source->nid);
        $query->propertyCondition('relation_type', 'has_role_in');
        $results = $query->execute();

        if ($results) {
            foreach (relation_load_multiple(array_keys($results)) as $relation) {
                $relation_node = relation_load($relation->rid);

                if ($relation_node->field_role['und'][0]['value'] == 'author') {
                    foreach (relation_get_endpoints($relation, 'node') as $related_nodes) {
                        foreach ($related_nodes as $related_node) {
                            if ($related_node->nid <> $source->nid) {
                                array_push($authors, $related_node->title);
                            }
                        }
                    }
                }
            }
        }
    }

    $value = count($authors) > 0 ? implode(", ", $authors) : NULL;
    return $value;
}

function get_languages($node)
{
    $field_language = str_replace(array("\n", "\r", ' '), '', $node->field_language['und'][0]['value']);

    # transform special cases or simply return value
    switch ($field_language) {
        case '':
            return [null];
        case 'GermanLatin':
        case 'LatinGerman':
            return array("tl" => ['German', 'Latin']);
        case 'EnglishLatin':
            return array("tl" => ['English', 'Latin']);
        case 'FrenchLatin':
            return array("tl" => ['French', 'Latin']);
        case "German,French,English,Italian":
            return array("tl" => ['German', 'French', 'English', 'Italian']);
        case "EnglishDutchLatinFrench":
            return array("tl" => ['English', 'Dutch', 'Latin', 'French']);
        case 'SpanishLatin':
            return array("tl" => ['Spanish', 'Latin']);
        default:
            return array("tl" => [$field_language]);
    }
}

function get_terms($node)
{

    $mentioned_names = [];

    // get related current names
    foreach (get_related_nodes('glossary_recipes', $node->nid) as $cn) {
        array_push($mentioned_names, get_clean_name($cn->title));
    }

    // get related historical names
    foreach (get_related_nodes('appellation_recipes', $node->nid) as $hn) {
        $name = get_clean_name($hn->title);
        if (!in_array($name, $mentioned_names)) {
            array_push($mentioned_names, $name);
        }

        foreach (get_related_nodes('glossary_appellation', $hn->nid) as $cn) {
            $name = get_clean_name($cn->title);
            if (!in_array($name, $mentioned_names)) {
                array_push($mentioned_names, $name);
            }
        }
    }

    $ids = [];

    foreach ($mentioned_names as $name) {
        if (!empty($name)) {
            $rkd_id = get_rkd_id($name);
            if ($rkd_id && !in_array($rkd_id, $ids)) {
                array_push($ids, $rkd_id);
            }
        }
    }

    return array("tW" => $ids);
}

function get_related_nodes($relation_type, $node_id)
{
    $relation_list = relation_load_multiple(array_keys(query_for_relations($relation_type, $node_id)));
    $related_nodes = extract_related_nodes($relation_list, $node_id);
    return $related_nodes;
}

function query_for_relations($relation_type, $node_id)
{
    $query = relation_query();
    $query->related('node', $node_id, NULL);
    $query->propertyCondition('relation_type', $relation_type);
    $result = $query->execute();

    return $result;
}

function extract_related_nodes($relation_list, $node_id)
{
    $result = array();

    foreach ($relation_list as $relation) {
        foreach (relation_get_endpoints($relation, 'node') as $related_nodes) {
            foreach ($related_nodes as $related_node) {
                if ($related_node->nid <> $node_id) {
                    array_push($result, $related_node);
                }
            }
        }
    }

    return $result;
}

function get_rkd_id($term)
{
    global $rkd_terms;

    foreach ($rkd_terms as $rkd_term) {
        if ($term == strtolower($rkd_term['term'])) {
            return ($rkd_term['id']);
        }
    }

    return null;
}

function get_clean_name($title)
{
    $index = strpos($title, "(");
    if ($index) {
        $title = substr($title, 0, $index - 1);
    }
    return strtolower(strip_whitespaces($title));
}

function strip_whitespaces($line)
{
    return trim(str_replace(array("\r", "\n"), '', $line));
}

#####################
### Create RKD excerpt as array

function get_rkd_excerpt($node)
{
    $languages = get_languages($node);
    $terms = get_terms($node);

    $rest = array(
        'an' => get_annotations($node),
        // 'bd' => get_starting_date($node),
        // 'ed' => get_end_date($node),
        'od' => get_date($node),
        'op' => get_remarks($node),
        'ur' => get_manuscript_link($node),
        'ti' => get_title($node),
        'ci' => get_transcription($node), // field_transcription =>  unicode escape characters are created by jsonencode() DO NOT USE IT!
        'su' => get_translation($node), // field_translation => unicode escape characters are created by jsonencode() DO NOT USE IT!
        'gh' => get_source_location($node),
        'pl' => get_source_place($node),
        'do' => 'Artechne',
        'vo' => 'recept',
    );

    return array_replace($languages, $terms, $rest);
}

####################
### EXPORT
function write_to_file($text)
{
    // echo $text;

    $file = create_dir(EXPORT_BASE, null) . "/artechne_export.dat";
    file_put_contents($file, $text, FILE_APPEND);
}

function load_rkd_terms()
{
    $json = file_get_contents(RKD_TERMS);
    return json_decode($json, true);
}


######################
### MAIN: entry point

function main()
{
    global $rkd_terms;
    $rkd_terms = load_rkd_terms();

    $nodes = get_nodes('recipes');

    // $tw_count = 0;

    foreach ($nodes as $node) {
        $n = node_load($node->nid);
        $rkd_excerpt_dict = get_rkd_excerpt($n);

        write_to_file("**\n");

        foreach ($rkd_excerpt_dict as $key => $value) {
            if (!is_null($value)) {
                if ($key == "tl") {
                    foreach ($value as $language) {
                        write_to_file($key . " " . $language . "\n");        
                    }
                }
                elseif ($key == 'tW') {
                    foreach ($value as $term) {
                        // $tw_count++;
                        write_to_file($key . " " . $term . "\n");        
                    }
                }                
                else {
                    write_to_file($key . " " . $value . "\n");   
                }                
            }
        }
    }

    // echo "Linked to " . $tw_count . " RKD terms\n";
}

main();

###########################################
### HELPERS

function get_nodes($type)
{
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'node')
        ->entityCondition('bundle', $type);
        // ->propertyCondition('nid', '888748'); //889001 87588 // example export: 94002 (lange tekst in transcriptie) // 89844, 89777 (coordinates) / cn 86815
    $result = $query->execute();

    $nodes = $result['node'];
    echo "Found " . count($nodes) . " nodes of type: " . $type . "\n";
    return $nodes;
}

function create_dir($base, $name)
{
    $dir = $base;

    if (!is_null($name)) {
        $dir = $base . $name . '/';
    }

    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    return $dir;
}

function echo_node($node)
{
    echo json_encode($node) . "\n";
}
