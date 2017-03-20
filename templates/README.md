This folder (and especially its sub-folders) contain customized templates for the ARTECHNE Drupal Bootstrap sub-theme. 
We'll review each of the files below: 

* node
  * `node--glossary.tpl.php`: Adds a variable (`$getty_results`) to display data from the [Getty AAT](http://www.getty.edu/research/tools/vocabularies/aat/) for the Glossary content type. This template is based upon Drupal Bootstrap's `node/node.tpl.php`.
  * `node--person.tpl.php`: Adds a variable (`$getty_results`) to display data from the [Getty ULAN](http://www.getty.edu/research/tools/vocabularies/ulan/) for the Person content type. This template is based upon Drupal Bootstrap's `node/node.tpl.php`.
* views
  * `views-view--search-api-page--page-2.tpl.php`: Instead of showing the search results, this view shows the geospatial and temporal metadata, using the visualization tool [GeoTemCo](http://www.informatik.uni-leipzig.de:8080/geotemco/). This template is based upon [Drupal Views](https://www.drupal.org/project/views)'s `views/view.tpl.php`.
