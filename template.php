<?php
/**
 * @file
 * The primary PHP file for this theme.
 */

// Add Open Sans/Merriweather fonts
function artechne_preprocess_html(&$vars) {
  drupal_add_css('//fonts.googleapis.com/css?family=Open+Sans:400,300,700|Merriweather:400,700,400italic&subset=latin,latin-ext', array(
      'type' => 'external'
    ));
}

// Change search form placeholder (doesn't work)
function artechne_form_alter(&$form, &$form_state, $form_id) {
  if ($form_id == 'search_api_page_search_form_search_artechne_database') {
    // HTML5 placeholder attribute
    $form['search_api_page_search_form_search_artechne_database']['#attributes']['placeholder'] = t('Search database');
  }
}

// Display the menu blocks as tabs
function artechne_menu_tree(&$variables) {
  return '<ul class="menu nav nav-tabs">' . $variables['tree'] . '</ul>';
}

/**
 * Add line breaks to transcription field
 */
function artechne_preprocess_field(&$variables, $hook) {
  if ($variables['element']['#field_name'] == 'field_transcription') {
    $variables['items'][0]['#markup'] = nl2br($variables['items'][0]['#markup']);
  }
}

/**
 * For Person and Glossary items, retrieve extra biographical details via SPARQL
 */
function artechne_preprocess_node(&$variables) {
  if ($variables['type'] == 'person' && $variables['field_getty_id']) _preprocess_person($variables);
  if ($variables['type'] == 'glossary' && $variables['field_getty_id']) _preprocess_glossary($variables);
}

/**
 * Retrieve biographical details on a Person from the Getty ULAN via a SPARQL query
 */
function _preprocess_person(&$variables) {
  $id = $variables['field_getty_id']['und'][0]['value'];

  $query = '
PREFIX schema: <http://schema.org/>
PREFIX rdfs:   <http://www.w3.org/2000/01/rdf-schema#>
PREFIX foaf:   <http://xmlns.com/foaf/0.1/>
PREFIX dc:     <http://purl.org/dc/elements/1.1/>
PREFIX xl:     <http://www.w3.org/2008/05/skos-xl#>
PREFIX gvp:    <http://vocab.getty.edu/ontology#>
SELECT DISTINCT * where {
  ?c gvp:prefLabelGVP/xl:literalForm ?lab .
  ?c foaf:focus/gvp:biographyPreferred ?bio .
  ?bio schema:description ?desc .
  ?bio schema:gender/rdfs:label ?gender . FILTER (lang(?gender) = "en") .
  ?bio gvp:estStart ?bd .
  OPTIONAL { ?bio schema:birthPlace/^foaf:focus/gvp:prefLabelGVP/xl:literalForm ?bl . FILTER (lang(?bl) = "en")} .
  ?bio gvp:estEnd ?dd .
  OPTIONAL { ?bio schema:deathPlace/^foaf:focus/gvp:prefLabelGVP/xl:literalForm ?dl . FILTER (lang(?dl) = "en")} .
  ?c dc:identifier "' . $id . '"
}
  ';

  $endpoint = sparql_registry_load_by_uri('http://vocab.getty.edu/sparql.json');
  $sparql_result = sparql_request($query, $endpoint);
  $result = json_decode($sparql_result['result'])->results->bindings[0];

  $html = '<h3>Information from the Getty ULAN</h3>';
  $html .= '<h4><a href="" target="_blank">' . $result->lab->value . '</a> (';
  $html .= $result->bd->value;
  if (isset($result->bl)) $html .= ' (' . $result->bl->value . ')';
  $html .= ' - ';
  $html .= $result->dd->value;
  if (isset($result->dl)) $html .= ' (' . $result->dl->value . ')';
  $html .= ')</h4>';
  $html .= '<p>' . $result->desc->value . '</p>';

  $variables['getty_results'] = $html; 
}

/**
 * Retrieve details on a Glossary item from the Getty AAT via a SPARQL query
 */
function _preprocess_glossary(&$variables) {
  $id = $variables['field_getty_id']['und'][0]['value'];

  $query = '
PREFIX schema: <http://schema.org/>
PREFIX rdfs:   <http://www.w3.org/2000/01/rdf-schema#>
PREFIX rdf:    <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX foaf:   <http://xmlns.com/foaf/0.1/>
PREFIX dc:     <http://purl.org/dc/elements/1.1/>
PREFIX xl:     <http://www.w3.org/2008/05/skos-xl#>
PREFIX gvp:    <http://vocab.getty.edu/ontology#>
PREFIX skos:   <http://www.w3.org/2004/02/skos/core#>
SELECT DISTINCT * where {
  ?c gvp:prefLabelGVP ?pref .
  ?pref xl:literalForm ?lab .
  ?c rdfs:seeAlso ?see .
  ?c skos:scopeNote ?n .
  ?n rdf:value ?desc . FILTER (lang(?desc) = "en") .
  ?c gvp:broaderPreferred ?p .
  ?p gvp:prefLabelGVP ?ppref .
  ?ppref xl:literalForm ?plab .
  ?c dc:identifier "' . $id . '"
}
  ';

  $endpoint = sparql_registry_load_by_uri('http://vocab.getty.edu/sparql.json');
  $sparql_result = sparql_request($query, $endpoint);
  $result = json_decode($sparql_result['result'])->results->bindings[0];

  $html = '<h3>Information from the Getty AAT</h3>';
  $html .= '<h4><a href="' . $result->see->value . '" target="_blank">' . $result->lab->value . '</a>';
  $html .= '<p>' . $result->desc->value . '</p>';

  $variables['getty_results'] = $html;
}

/* Extra facets (doesn't work)
function artechne_facetapi_facet_info($searcher_info) {
   $facets = array();

   $facets['glossary'] = array(
       'label' => t('Glossary'),
       'description' => t('Filter by Glossary Entry.'),
       'field' => 'rel_glossary_recipes',
       'facet mincount allowed' => TRUE,
       'dependency plugins' => array('bundle', 'role'),
   );


   $facets['artistic_techniques'] = array(
       'label' => t('Artistic Techniques'),
       'description' => t('Filter by Artistic Techniques.'),
       'field' => 'tosnl_field_artistic_techniques',
       'facet mincount allowed' => TRUE,
       'dependency plugins' => array('bundle', 'role'),
   );

  return $facets;
}*/

